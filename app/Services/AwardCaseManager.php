<?php namespace App\Services;

use Carbon\Carbon;
use App\Services\Service;

use DB;
use Auth;
use Config;
use Notifications;

use App\Models\User\User;
use App\Models\Award\Award;
use App\Models\Award\AwardCategory;
use App\Models\User\UserAward;
use App\Models\Character\CharacterAward;

class AwardCaseManager extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Award Case Manager
    |--------------------------------------------------------------------------
    |
    | Handles modification of user-owned awards.
    |
    */

    /**
     * Grants an award to multiple users.
     *
     * @param  array                 $data
     * @param  \App\Models\User\User $staff
     * @return bool
     */
    public function grantAwards($data, $staff)
    {
        DB::beginTransaction();

        try {
            foreach($data['quantities'] as $q) {
                if($q <= 0) throw new \Exception("All quantities must be at least 1.");
            }

            // Process names
            $users = User::find($data['names']);
            if(count($users) != count($data['names'])) throw new \Exception("An invalid user was selected.");

            $keyed_quantities = [];
            array_walk($data['award_ids'], function($id, $key) use(&$keyed_quantities, $data) {
                if($id != null && !in_array($id, array_keys($keyed_quantities), TRUE)) {
                    $keyed_quantities[$id] = $data['quantities'][$key];
                }
            });

            // Process award
            $awards = Award::find($data['award_ids']);
            if(!count($awards)) throw new \Exception("No valid awards found.");

            foreach($users as $user) {
                foreach($awards as $award) {
                    if($this->creditAward($staff, $user, 'Staff Grant', array_only($data, ['data', 'disallow_transfer', 'notes']), $award, $keyed_quantities[$award->id]))
                    {
                        Notifications::create('AWARD_GRANT', $user, [
                            'award_name' => $award->name,
                            'award_quantity' => $keyed_quantities[$award->id],
                            'sender_url' => $staff->url,
                            'sender_name' => $staff->name
                        ]);
                    }
                    else
                    {
                        throw new \Exception("Failed to credit awards to ".$user->name.".");
                    }
                }
            }
            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Grants an award to a character.
     *
     * @param  array                            $data
     * @param  \App\Models\Character\Character  $character
     * @param  \App\Models\User\User            $staff
     * @return bool
     */
    public function grantCharacterAwards($data, $character, $staff)
    {
        DB::beginTransaction();

        try {
            if(!$character) throw new \Exception("Invalid character selected.");

            foreach($data['quantities'] as $q) {
                if($q <= 0) throw new \Exception("All quantities must be at least 1.");
            }

            $keyed_quantities = [];
            array_walk($data['award_ids'], function($id, $key) use(&$keyed_quantities, $data) {
                if($id != null && !in_array($id, array_keys($keyed_quantities), TRUE)) {
                    $keyed_quantities[$id] = $data['quantities'][$key];
                }
            });

            // Process award(s)
            $awards = Award::find($data['award_ids']);
            foreach($awards as $i) {
                if(!$i->category->is_character_owned) throw new \Exception("One of these awards cannot be owned by characters.");
            }
            if(!count($awards)) throw new \Exception("No valid awards found.");
            
            foreach($awards as $award) {
                $this->creditAward($staff, $character, 'Staff Grant', array_only($data, ['data', 'disallow_transfer', 'notes']), $award, $keyed_quantities[$award->id]);
                if($character->is_visible && $character->user_id) {
                    Notifications::create('CHARACTER_AWARD_GRANT', $character->user, [
                        'award_name' => $award->name,
                        'award_quantity' => $keyed_quantities[$award->id],
                        'sender_url' => $staff->url,
                        'sender_name' => $staff->name,
                        'character_name' => $character->fullName,
                        'character_slug' => $character->slug,
                    ]);
                }
            }
            
            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Transfers awards between a user and character.
     *
     * @param  \App\Models\User\User|\App\Models\Character\Character          $sender
     * @param  \App\Models\User\User|\App\Models\Character\Character          $recipient
     * @param  \App\Models\User\UserAward|\App\Models\Character\CharacterAward  $stacks
     * @param  int                                                            $quantities
     * @return bool
     */
    public function transferCharacterStack($sender, $recipient, $stacks, $quantities)
    {
        DB::beginTransaction();

        try {
            foreach($stacks as $key=>$stack) {
                $quantity = $quantities[$key];
                if(!$recipient) throw new \Exception("Invalid recipient selected.");
                if(!$sender) throw new \Exception("Invalid sender selected.");
                if($recipient->logType == 'Character' && $sender->logType == 'Character') throw new \Exception("Cannot transfer awards between characters.");
                if($recipient->logType == 'Character' && !$sender->hasPower('edit_awardcases') && !$recipient->is_visible) throw new \Exception("Invalid character selected.");
                if(!$stacks) throw new \Exception("Invalid stack selected.");
                if($sender->logType == 'Character' && $quantity <= 0 && $stack->count > 0) $quantity = $stack->count;
                if($quantity <= 0) throw new \Exception("Invalid quantity entered.");
                
                if($recipient->logType == 'Character' && !$stack->award->category->is_character_owned) throw new \Exception("One of the selected awards cannot be owned by characters.");
                if((!$stack->award->allow_transfer || isset($stack->data['disallow_transfer'])) && !Auth::user()->hasPower('edit_awardcases')) throw new \Exception("One of the selected awards cannot be transferred.");
                if($stack->count < $quantity) throw new \Exception("Quantity to transfer exceeds award count.");

                //Check that hold count isn't being exceeded
                if($stack->award->category->character_limit > 0) $limit = $stack->award->category->character_limit;
                if($recipient->logType == 'Character' && isset($limit)) {
                    $limitedAwards = Award::where('award_category_id', $stack->award->category->id);
                    $ownedLimitedAwards = CharacterAward::with('award')->whereIn('award_id', $limitedAwards->pluck('id'))->whereNull('deleted_at')->where('count', '>', '0')->where('character_id', $recipient->id)->get();
                    $newOwnedLimit = $ownedLimitedAwards->pluck('count')->sum() + $quantity;
                }

                if($recipient->logType == 'Character' && isset($limit) && ($ownedLimitedAwards->pluck('count')->sum() >= $limit || $newOwnedLimit > $limit)) throw new \Exception("One of the selected awards exceeds the limit characters can own for its category.");

                $this->creditAward($sender, $recipient, $sender->logType == 'User' ? 'User ??? Character Transfer' : 'Character ??? User Transfer', $stack->data, $stack->award, $quantity);

                $stack->count -= $quantity;
                $stack->save();
            }
            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Transfers awards between user stacks.
     *
     * @param  \App\Models\User\User      $sender
     * @param  \App\Models\User\User      $recipient
     * @param  \App\Models\User\UserAward  $stacks
     * @param  int                        $quantities
     * @return bool
     */
    public function transferStack($sender, $recipient, $stacks, $quantities)
    {
        DB::beginTransaction();

        try {
            foreach($stacks as $key=>$stack) {
                $quantity = $quantities[$key];
                if(!$sender->hasAlias) throw new \Exception("Your deviantART account must be verified before you can perform this action.");
                if(!$stack) throw new \Exception("An invalid award was selected.");
                if($stack->user_id != $sender->id && !$sender->hasPower('edit_awardcases')) throw new \Exception("You do not own one of the selected awards.");
                if($stack->user_id == $recipient->id) throw new \Exception("Cannot send awards to the award's owner.");
                if(!$recipient) throw new \Exception("Invalid recipient selected.");
                if(!$recipient->hasAlias) throw new \Exception("Cannot transfer awards to a non-verified member.");
                if($recipient->is_banned) throw new \Exception("Cannot transfer awards to a banned member.");
                if((!$stack->award->allow_transfer || isset($stack->data['disallow_transfer'])) && !$sender->hasPower('edit_awardcases')) throw new \Exception("One of the selected awards cannot be transferred.");
                if($stack->count < $quantity) throw new \Exception("Quantity to transfer exceeds award count.");

                $oldUser = $stack->user;
                if($this->moveStack($stack->user, $recipient, ($stack->user_id == $sender->id ? 'User Transfer' : 'Staff Transfer'), ['data' => ($stack->user_id != $sender->id ? 'Transferred by '.$sender->displayName : '')], $stack, $quantity)) 
                {
                    Notifications::create('AWARD_TRANSFER', $recipient, [
                        'award_name' => $stack->award->name,
                        'award_quantity' => $quantity,
                        'sender_url' => $sender->url,
                        'sender_name' => $sender->name
                    ]);
                    if($stack->user_id != $sender->id) 
                        Notifications::create('FORCED_AWARD_TRANSFER', $oldUser, [
                            'award_name' => $stack->award->name,
                            'award_quantity' => $quantity,
                            'sender_url' => $sender->url,
                            'sender_name' => $sender->name
                        ]);
                }
            }
            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Deletes awards from stack.
     *
     * @param  \App\Models\User\User|\App\Models\Character\Character          $owner
     * @param  \App\Models\User\UserAward|\App\Models\Character\CharacterAward  $stacks
     * @param  int                                                            $quantities
     * @return bool
     */
    public function deleteStack($owner, $stacks, $quantities)
    {
        DB::beginTransaction();

        try {
            if($owner->logType == 'User') {
                foreach($stacks as $key=>$stack) {
                    $user = Auth::user();
                    $quantity = $quantities[$key];
                    if(!$owner->hasAlias) throw new \Exception("Your deviantART account must be verified before you can perform this action.");
                    if(!$stack) throw new \Exception("An invalid award was selected.");
                    if($stack->user_id != $owner->id && !$user->hasPower('edit_awardcases')) throw new \Exception("You do not own one of the selected awards.");
                    if($stack->count < $quantity) throw new \Exception("Quantity to delete exceeds award count.");
                    
                    $oldUser = $stack->user;

                    if($this->debitStack($stack->user, ($stack->user_id == $user->id ? 'User Deleted' : 'Staff Deleted'), ['data' => ($stack->user_id != $user->id ? 'Deleted by '.$user->displayName : '')], $stack, $quantity)) 
                    {
                        if($stack->user_id != $user->id) 
                            Notifications::create('AWARD_REMOVAL', $oldUser, [
                                'award_name' => $stack->award->name,
                                'award_quantity' => $quantity,
                                'sender_url' => $user->url,
                                'sender_name' => $user->name
                            ]);
                    }
                }
            }
            else {
                foreach($stacks as $key=>$stack) {
                    $quantity = $quantities[$key];
                    $user = Auth::user();
                    if(!$user->hasAlias) throw new \Exception("Your deviantART account must be verified before you can perform this action.");
                    if(!$stack) throw new \Exception("An invalid award was selected.");
                    if($stack->character->user_id != $user->id && !$user->hasPower('edit_awardcases')) throw new \Exception("You do not own one of the selected awards.");
                    if($stack->count < $quantity) throw new \Exception("Quantity to delete exceeds award count.");

                    if($this->debitStack($stack->character, ($stack->character->user_id == $user->id ? 'User Deleted' : 'Staff Deleted'), ['data' => ($stack->character->user_id != $user->id ? 'Deleted by '.$user->displayName : '')], $stack, $quantity)) 
                    {
                        if($stack->character->user_id != $user->id && $stack->character->is_visible && $stack->character->user_id)
                            Notifications::create('CHARACTER_AWARD_REMOVAL', $stack->character->user, [
                                'award_name' => $stack->award->name,
                                'award_quantity' => $quantity,
                                'sender_url' => $user->url,
                                'sender_name' => $user->name,
                                'character_name' => $stack->character->fullName,
                                'character_slug' => $stack->character->slug
                            ]);
                    }
                }
            }
            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Credits an award to a user or character.
     *
     * @param  \App\Models\User\User|\App\Models\Character\Character  $sender
     * @param  \App\Models\User\User|\App\Models\Character\Character  $recipient
     * @param  string                                                 $type 
     * @param  array                                                  $data
     * @param  \App\Models\Award\Award                                  $award
     * @param  int                                                    $quantity
     * @return bool
     */
    public function creditAward($sender, $recipient, $type, $data, $award, $quantity)
    {
        DB::beginTransaction();

        try {
            $encoded_data = \json_encode($data);

            if($recipient->logType == 'User') {
                $recipient_stack = UserAward::where([
                    ['user_id', '=', $recipient->id],
                    ['award_id', '=', $award->id],
                    ['data', '=', $encoded_data]
                ])->first();
                
                if(!$recipient_stack)
                    $recipient_stack = UserAward::create(['user_id' => $recipient->id, 'award_id' => $award->id, 'data' => $encoded_data]);
                $recipient_stack->count += $quantity;
                $recipient_stack->save();
            }
            else {
                $recipient_stack = CharacterAward::where([
                    ['character_id', '=', $recipient->id],
                    ['award_id', '=', $award->id],
                    ['data', '=', $encoded_data]
                ])->first();
                
                if(!$recipient_stack)
                    $recipient_stack = CharacterAward::create(['character_id' => $recipient->id, 'award_id' => $award->id, 'data' => $encoded_data]);
                $recipient_stack->count += $quantity;
                $recipient_stack->save();
            }
            if($type && !$this->createLog($sender ? $sender->id : null, $sender ? $sender->logType : null, $recipient ? $recipient->id : null, $recipient ? $recipient->logType : null, null, $type, $data['data'], $award->id, $quantity)) throw new \Exception("Failed to create log.");

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Moves awards from one user or character stack to another.
     *
     * @param  \App\Models\User\User|\App\Models\Character\Character          $sender
     * @param  \App\Models\User\User|\App\Models\Character\Character          $recipient
     * @param  string                                                         $type 
     * @param  array                                                          $data
     * @param  \App\Models\User\UserAward|\App\Models\Character\CharacterAward  $award
     * @return bool
     */
    public function moveStack($sender, $recipient, $type, $data, $stack, $quantity)
    {
        DB::beginTransaction();

        try {
            $recipient_stack = UserAward::where([
                ['user_id', '=', $recipient->id],
                ['award_id', '=', $stack->award_id],
                ['data', '=', json_encode($stack->data)]
            ])->first();

            if(!$recipient_stack)
                $recipient_stack = UserAward::create(['user_id' => $recipient->id, 'award_id' => $stack->award_id, 'data' => json_encode($stack->data)]);
                
            $stack->count -= $quantity;
            $recipient_stack->count += $quantity;
            $stack->save();
            $recipient_stack->save();

            if($type && !$this->createLog($sender ? $sender->id : null, $sender ? $sender->logType : null, $recipient->id, $recipient ? $recipient->logType : null, $stack->id, $type, $data['data'], $stack->award_id, $quantity)) throw new \Exception("Failed to create log.");

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Debits an award from a user or character.
     *
     * @param  \App\Models\User\User|\App\Models\Character\Character  $owner
     * @param  string                                                 $type 
     * @param  array                                                  $data
     * @param  \App\Models\Award\UserAward                              $stack
     * @return bool
     */
    public function debitStack($owner, $type, $data, $stack, $quantity)
    {
        DB::beginTransaction();

        try {
            $stack->count -= $quantity;
            $stack->save();

            if($type && !$this->createLog($owner ? $owner->id : null, $owner ? $owner->logType : null, null, null, $stack->id, $type, $data['data'], $stack->award->id, $quantity)) throw new \Exception("Failed to create log."); 

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Creates an award case log.
     *
     * @param  int     $senderId
     * @param  string  $senderType
     * @param  int     $recipientId
     * @param  string  $recipientType
     * @param  int     $stackId
     * @param  string  $type 
     * @param  string  $data
     * @param  int     $quantity
     * @return  int
     */
    public function createLog($senderId, $senderType, $recipientId, $recipientType, $stackId, $type, $data, $awardId, $quantity)
    {
        
        return DB::table('awards_log')->insert(
            [
                'sender_id' => $senderId,
                'sender_type' => $senderType,
                'recipient_id' => $recipientId,
                'recipient_type' => $recipientType,
                'stack_id' => $stackId,
                'log' => $type . ($data ? ' (' . $data . ')' : ''),
                'log_type' => $type,
                'data' => $data, // this should be just a string
                'award_id' => $awardId,
                'quantity' => $quantity,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        );
    }
}