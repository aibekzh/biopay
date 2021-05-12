<?php


namespace App\Rules;


use Illuminate\Contracts\Validation\ImplicitRule;
use Illuminate\Support\Facades\Hash;

class DontMatchOldPassword implements ImplicitRule
{

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return !Hash::check($value, auth()->user()->password);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Введенный пароль совпадает со старым паролем.';
    }
}
