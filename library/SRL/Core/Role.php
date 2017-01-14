<?php
abstract class SRL_Core_Role
{
    const Admin = 1;
    const Operator = 2;
    const Halfoperator = 3;
    const Voice = 4;
    const Anon = 5;
    
    public static function GetRole($role_id)
    {
        switch($role_id)
        {
            case 1:
                return 'admin';
                break;
            case 2:
                return 'op';
                break;
            case 3:
                return 'halfop';
                break;
            case 4:
                return 'voice';
                break;
            case 5:
                return 'user';
                break;
            default:
                return 'anon';
        }
    }
}