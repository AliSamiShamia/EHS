<?php

namespace App\Services\Facades;

use App\Helper\_RuleHelper;
use App\Helpers\_MailHelper;
use App\Models\User;
use App\Models\UserCode;
use App\Services\Interfaces\UserInterface;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserFacade extends BaseFacade implements UserInterface
{


    public function __construct()
    {
        $this->setModel(User::class);
        $this->setColumns([
            "name", "email", "hospital", "bayanati_number", "speciality", "code_id"
        ]);
        $this->setRules([
            "name" => _RuleHelper::_Rule_Require,
            "email" => _RuleHelper::_Rule_Require . "|" . _RuleHelper::_Rule_Email,
            "hospital" => _RuleHelper::_Rule_Require,
            "speciality" => _RuleHelper::_Rule_Require,
            "code" => _RuleHelper::_Rule_Require,
        ]);
        $this->setWhere([]);
        $this->setHash(true);
        $this->setHashColumn("password");
        $this->setUnique(true);
        $this->setUniqueColumn("email");
        $this->setOrderBy("asc");
    }

    function store(Request $request)
    {
        $request->validate($this->getRules());
        $code = $this->getCode($request->input('code'));
        if (!$code) {
            throw new Exception("Registration code is not exist");
        }
        $user = $this->checkCodeDuplicate($code);
        if ($user) {
            throw new Exception("Registration code is already taken");
        }

        return User::query()->create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'hospital' => $request->input('hospital'),
            'bayanati_number' => $request->input('bayanati_number'),
            'speciality' => $request->input('speciality'),
            'code_id' => $code->id,
        ]);
    }

    function getCode($code): Model|Builder|null
    {
        return UserCode::query()->where([
            'code' => $code
        ])->first();
    }

    function checkCodeDuplicate($code): ?Model
    {
        return $this->getOneByColumns([
            'code_id' => $code->id,
        ]);
    }

    function login(Request $request): ?Model
    {
        $rules = [
            "email" => _RuleHelper::_Rule_Require,
            "password" => _RuleHelper::_Rule_Require,
        ];
        $request->validate($rules);
        $user = $this->getOneByColumns([
            "email" => $request->input('email')
        ]);
        if ($user) {
            $check = Hash::check($user->salt_hash . $request->input('password'), $user->password);
            return $check ? $user : null;
        }
        return null;
    }

    function loginByEmail(Request $request): ?Model
    {
        $rules = [
            "email" => _RuleHelper::_Rule_Require,
        ];
        $request->validate($rules);
        return $this->getOneByColumns([
            "email" => $request->input('email')
        ]);
    }

    function loginById($id): ?Model
    {
        return $this->getOneByColumns([
            "id" => $id
        ]);
    }

    function forgotPassword(Request $request): bool
    {
        $rules = [
            "email" => _RuleHelper::_Rule_Require,
        ];
        $request->validate($rules);
        $user = $this->getOneByColumns([
            "email" => $request->input("email")
        ]);
        if ($user) {
            _MailHelper::forgotEmail($user);
            return true;
        }
        return false;
    }

    function resetPassword(Request $request)
    {
        $rules = [
            "token" => _RuleHelper::_Rule_Require,
            "password" => _RuleHelper::_Rule_Require,
            "confirm_password" => _RuleHelper::_Rule_Require,
        ];
        $request->validate($rules);

        if ($request->input('password') == $request->input('confirm_password')) {

        }
    }
}
