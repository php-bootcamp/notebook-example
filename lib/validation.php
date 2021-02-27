<?php

interface ValidatorInterface
{
    static function validate($value, ...$parameters);
}

class RuleNotValidException extends InvalidArgumentException
{
    public function __construct($rule)
    {
        parent::__construct("Rule handler of `${rule}` does not exists");
    }
}

class ValidationException extends Exception
{
    protected $errors;

    public function __construct($errors)
    {
        parent::__construct("Input data is not valid");
        $this->errors = $errors;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}

class AuthenticationFailedException extends Exception
{
    public function __construct()
    {
        parent::__construct("Couldn't authenticate with given credentials");
    }
}

class RequiredValidator implements ValidatorInterface
{
    public static function validate($value, ...$parameters)
    {
        return strlen($value) > 0;
    }
}

class MinLengthValidator implements ValidatorInterface
{

    public static function validate($value, ...$parameters)
    {
        if (empty($value)) return true;

        $length = intval($parameters[0]);

        return $length <= strlen($value);
    }
}

class Validator
{
    protected $rules;
    protected $fields;
    protected $errors = [];

    protected function __construct($rules, $fields)
    {
        $this->rules = $rules;
        $this->fields = $fields;
    }

    public static function getRuleHandlers()
    {
        return [
            'required' => [RequiredValidator::class, 'validate'],
            'min_length' => [MinLengthValidator::class, 'validate'],
        ];
    }

    public static function create($rules, $fields)
    {
        return new static($rules, $fields);
    }

    /**
     * @throws RuleNotValidException
     */
    public function checkErrors()
    {
        $handlers = static::getRuleHandlers();

        foreach ($this->rules as $field_name => $rule_group) {
            $value = null;
            if (key_exists($field_name, $this->fields)) {
                $value = $this->fields[$field_name];
            }

            $rules = explode('|', $rule_group);

            foreach ($rules as $rule) {
                $name = $rule;
                $parameters = [];
                if (strpos($rule, ':')) {
                    [$name, $parameters_group] = explode(":", $rule);
                    $parameters = explode(",", $parameters_group);
                }
                if (!key_exists($name, $handlers)) {
                    throw new RuleNotValidException($name);
                }

                if (!$handlers[$name]($value, ...$parameters)) {
                    $this->errors[$field_name][] = $name;
                }
            }
        }
    }

    /**
     * @throws ValidationException
     */
    public function validate()
    {
        $this->checkErrors();
        if (count($this->errors) > 0) {
            throw new ValidationException($this->errors);
        }
    }
}

$correct_rules = ['username' => 'required', 'password' => 'required|min_length:5'];
$incorrect_rules = ['username' => 'required', 'password' => 'required|min_length:5|non_exist_rule'];

function login($username, $password, $rules = [])
{
    $validator = Validator::create(
        $rules,
        ['username' => $username, 'password' => $password],
    );
    try {
        $validator->validate();
        if ($username === "orkun" && $password === "12345") {
            var_dump("Successful login");
        } else {
            throw new AuthenticationFailedException();
        }
        var_dump("Login successful");
    } catch (ValidationException $e) {
        var_dump("Validation Exception", $e->getErrors());
    } catch (AuthenticationFailedException $e) {
        var_dump("Authentication Failed Exception", $e->getMessage());
    } catch (Exception $e) {
        var_dump("Unknown exception", $e->getMessage());
    }
}

// login("orkun", null, $correct_rules); // ValidationException: password required
// login(null, null, $correct_rules); // ValidationException: username required, password required
// login("orkun", "123", $correct_rules); // ValidationException password min_length
// login("incorrect", "credentials", $correct_rules); // AuthenticationFailedException
// login("orkun", "12345", $incorrect_rules); // UnknownException
// login("orkun", "12345", $correct_rules); // Successful login
