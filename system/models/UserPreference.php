<?php
use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{

    public static function getPreference($user_id, $preference)
    {
        $preference = self::where('user_id', $user_id)->where('preference', $preference)->first();
        if($preference){
            return $preference->value;
        }
        return null;
    }

    public static function setPreference($user_id, $preference_key, $value)
    {
        $preference = self::where('user_id', $user_id)->where('preference', $preference_key)->first();
        if(!$preference){
            $preference = new self();
            $preference->user_id = $user_id;
            $preference->preference = $preference_key;
        }
        $preference->value = $value;
        $preference->save();
        return $preference;
    }

    public static function getAllPreferences($user_id)
    {
        $result = [];
        $preferences = self::where('user_id', $user_id)->get();

        foreach ($preferences as $preference) {
            $result[$preference->preference] = $preference->value;
        }

        return $result;

    }

}