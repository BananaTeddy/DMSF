<?php declare(strict_types=1);
namespace system\Validators;

use system\Interfaces\IValidator;

class EmailValidator implements IValidator {
    /**
     * Checks if the email is valid
     * 
     * @param mixed $email
     * 
     * @return bool
     */
    public function isValid($email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
