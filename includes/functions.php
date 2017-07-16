<?php

session_start();
ob_start();

define('STORAGE_PATH', '../../data/');

// Prevent session hi-jacking, ideally use IP address as well
if (isset($_SESSION['user_agent']) === true &&
    $_SESSION['user_agent'] != $_SERVER['HTTP_USER_AGENT']) {
    logout();
}

/**
 * Generate an error which references the error code
 *
 * -1 Failed to acquire handle
 * -2 Failed to deserializing data
 * -3 Failed to serialize data
 *
 * @param integer $errorCode
 * @return array
 */
function generate_error($errorCode) {
    return array(
        'is currently experiencing technical difficulties, please try again later, error code: '. $errorCode
    );
}

/**
 * Load a file and return the contents in an array
 *
 * @param string $file
 * @return array[]|boolean
 */
function storage_load($file) {
    $handle = fopen(STORAGE_PATH . $file, 'r');

    if (!$handle) {
        return -1;
    }

    $data = '';
    while (!feof($handle)) {
        $data .= fgets($handle);
    }
    fclose($handle);

    if (strlen($data) === 0) {
        return array();
    }

    $unserialized = unserialize($data);
    if ($unserialized === false) {
        return -2;
    }

    return $unserialized;
}


/**
 * Save a file and return whether it was successful or not
 *
 * @param string $file
 * @param array[] $data
 * @return boolean
 */
function storage_save($file, array $data) {
    $serialized = serialize($data);
    if ($serialized === false) {
        return -3;
    }

    $handle = fopen(STORAGE_PATH . $file, 'w');
    if (!$handle) {
        return -1;
    }

    fwrite($handle, $serialized);
    fclose($handle);

    return true;
}


/**
 * Login the current user
 *
 * @param array $form
 * @return array|boolean
 */
function login($form) {
    $errors = array();

    $username = filter_username($form['username']);
    $password = filter_password($form['password']);

    $errors['username'] = validate_username($username);
    $errors['password'] = validate_password($password);

    $password = hash_password($password);
    if (username_exists($username) === false) {
        $errors['username'][] = ' and password do not match';
        return $errors;
    }

    $errors = array_filter($errors);
    if (count($errors) > 0) {
        return $errors;
    }

    $user = storage_load($username);

    if (empty($user)) {
        $errorCode = $user;
        $errors['website'] = generate_error($errorCode);
        return $errors;
    }

    if ($user['password'] !== $password) {
        $errors['username'][] = ' and username do not match';
        return $errors;
    }

    authenticate($user);

    return true;
}



function authenticate($user) {
    // Ideally prevent session fixation
    // https://www.owasp.org/index.php/Session_fixation
    session_regenerate_id(true);

    $_SESSION['user'] = array(
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'username'   => $user['username'],
        'full_name'  => $user['full_name'],
    );
}

/**
 * Logout the current user
 *
 * @return boolean
 */
function logout() {
    // Clear all data
    session_unset();

    // Clear cookies
    if (ini_get('session.use_cookies')) {
        $yesterday = time() - (24 * 60 * 60);

        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            $yesterday,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    // Clear session file
    session_destroy();

    // Change session id to prevent session hi-jacking
    session_regenerate_id(true);
}


/**
 * Validate username
 *
 * @param string $username
 * @return boolean
 */
function validate_username($username) {
    $errors = array();

    // Must not be empty
    if (empty($username)) {
        $errors[] = 'must not be empty';
    }

    // Must be > 2 characters and =< 32
    else if (strlen($username) < 2 || strlen($username) > 32) {
        $errors[] = 'must be between 2 and 32 characters';
    }

    // Must be alphabetical
    else if (!ctype_alpha($username)) {
        $errors[] = 'must be alphabetical';
    }

    return $errors;
}

/**
 * Username exists?
 *
 * @param string $username
 * @return boolean
 */
function username_exists($username) {
    return file_exists(STORAGE_PATH . $username);
}

/**
 * Filter whitespace and HTML tags
 *
 * @param string $value
 * @return string
 */
function filter($value) {
    $value = trim($value);
    $value = strip_tags($value);

    return $value;
}

/**
 * Escape HTML entities
 *
 * @param string $value
 * @return string
 */
function escape($value) {
    return htmlentities($value, ENT_QUOTES, 'UTF-8');
}


/**
 * Filter username
 *
 * @param string $username
 * @return string
 */
function filter_username($username) {
    $username = strtolower($username);

    return filter($username);
}

/**
 * Validate password
 *
 * @param string $password
 * @return array
 */
function validate_password($password) {
    $errors = array();

    // Must not be empty
    if (empty($password)) {
        $errors[] = 'must not be empty';
    }

    // Must not greater than or equal to six characters
    else if (strlen($password) < 6) {
        $errors[] = 'must be at least 6 characters';
    }

    // Ideally check for alpha / numeric but this does not guarantee a strong password

    return $errors;
}


/**
 * Filter password
 *
 * @param string $password
 * @return string
 */
function filter_password($password) {
    return $password;
}

/**
 * Filter password
 *
 * @param string $password
 * @return string
 */
function hash_password($password) {
    // Ideally use a unique salt per user
    return crypt($password, '$10a$gQJ8qQtkTve242NUc4qXUa');
}


/**
 * Validate email
 *
 * @param string $email
 * @return array
 */
function validate_email($email) {
    $errors = array();

    // Must not be empty
    if (empty($email)) {
        $errors[] = 'must not be empty';
    }

    // This is not a very strong validation as foo@bar.tld will pass but "tld" is an invalid top level domain
    // a better test would be sending an email for them to verify their email address
    else if (filter_var($email, FILTER_VALIDATE_EMAIL) !== $email) {
        $errors[] = 'must be in a valid format';
    }

    return $errors;
}


/**
 * Filter email
 *
 * @param string $email
 * @return string
 */
function filter_email($email) {
    return filter($email);
}


/**
 * Validate name
 *
 * @param string $name
 * @return array
 */
function validate_name($name) {
    $errors = array();

    // Must not be empty
    if (empty($name)) {
        $errors[] = 'must not be empty';
    }

    // Must not greater than or equal to sixty four characters
    // prevent denial of service by saving very long name and exhausting storage
    // storage
    else if (strlen($name) > 64) {
        $errors[] = 'must be under 64 characters';
    }

    $fragments = explode(' ', $name);

    // Remove empty string
    $fragments = array_filter($fragments);

    foreach ($fragments as $fragment) {
        // Ideally supports foreign names
        if (!ctype_alpha($fragment)) {
            $errors[] = 'must be alphabetical';
            return $errors;
        }
    }

    return $errors;
}

/**
 * Filter name
 *
 * @param string $name
 * @return string
 */
function filter_name($name) {
    $name = filter($name);
    $name = ucwords($name);

    return $name;
}

/**
 * Register a user
 *
 * @param array $form
 * @return array
 */
function register($form) {
    $errors = array();

    $username = filter_username($form['username']);
    $password = filter_password($form['password']);
    $email = filter_email($form['email']);
    $fullName = filter_name($form['full_name']);

    $errors['username'] = validate_username($username);
    $errors['password'] = validate_password($password);
    $errors['email'] = validate_email($email);
    $errors['name'] = validate_name($fullName);

    $errors = array_filter($errors);
    if (count($errors) > 0) {
        return $errors;
    }

    // This must come after otherwise it may validate an empty string as valid user
    if (username_exists($username)) {
        $errors['username'][] = ' is already used';
        return $errors;
    }

    $user = array(
        'username'  => $username,
        'password'  => hash_password($password),
        'email'     => $email,
        'full_name' => $fullName,
    );

    $result = storage_save($username, $user);
    if ($result !== true) {
        $errorCode = $result;
        $errors['website'] = generate_error($errorCode);
        return $errors;
    }

    // Login
    authenticate($user);

    return true;
}


/**
 * Get current logged in user
 *
 * @return array|null
 */
function get_session_user() {
    if (!empty($_SESSION['user'])) {
        return $_SESSION['user'];
    } else {
        return null;
    }
}


/**
 * Get whether session is authenticated
 *
 * @return boolean
 */
function is_authenticated() {
    return get_session_user() !== null;
}



/**
 * Render form errors
 *
 * @param array|null $errors
 * @return string
 */
function render_errors($errors = null) {
    if (empty($errors) || count($errors) === 0) {
        return '';
    }

    $html = "<ul>\n";
    foreach ($errors as $field => $fieldErrors) {
        foreach ($fieldErrors as $fieldError) {
            $html .= "  <li>\n";
            $html .= $field.' '. $fieldError;
            $html .= "  </li>\n";
        }
    }

    $html .= "</ul>\n";

    return $html;
}


/**
 * Render label
 *
 * @param string $id
 * @param string $label
 */
function render_label($id, $label) {
    $html  = "<dt>\n";
    $html .= "  <label for=\"".$id."\">".$label.":</label>\n";
    $html .= "</dt>\n";

    return $html;
}

/**
 * Render input
 *
 * @param string $id
 * @param string $type
 * @param string|null $value
 * @param string $helpText
 * @return string
 */
function render_input($id, $type, $value = null, $helpText = '') {
    $html  = "<dd>\n";
    $html .= "  <input id=\"{$id}\" name=\"{$id}\" type=\"{$type}\" value=\"{$value}\">\n";
    $html .= "  <span>{$helpText}</span>\n";
    $html .= "</dd>\n";

    return $html;
}

/**
 * Render field
 *
 * @param string $id
 * @param string $type
 * @param string $label
 * @param string $helpText
 * @return string
 */
function render_field($id, $type, $label, $helpText = '') {
    if (isset($_POST[$id])) {
        $value = escape(
            filter($_POST[$id])
        );
    } else {
        $value = '';
    }

    $html = '';
    $html .= render_label($id, $label);
    $html .= render_input($id, $type, $value, $helpText);

    return $html;
}

/**
 * Generate a url with the SID appended
 *
 * This should be avoided as it will break HTTP only and allow for XSS session
 * hi-jacking https://www.owasp.org/index.php/Session_hijacking_attack
 *
 * @param string $url
 * @return string
 */
function url($url) {
    return $url.'?'.htmlspecialchars(SID);
}
