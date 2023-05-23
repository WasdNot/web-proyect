<?php
define("CONNECTIONS", 0);

// Connection functions

function send_attempt($user, $password, $address, $result, mysqli $conn)
{
    $current_date = date('Y-m-d H:i:s');
    $sql = "INSERT INTO attempts (ip, user, password, date, result)" .
        "VALUES('$address', '$user', '$password', '$current_date', '$result');";

    if ($conn->connect_errno) {
        error("Hubo en error intentado establecer una conexión con la base de datos 'connections':"
            . $conn->error);
    }
    if (!$conn->query($sql)) {
        error("No se pudo establecer el registro correctamente en la base de datos 'connections':"
            . $_SERVER['REMOTE_ADDR']);
    }

    $conn->close();
}

function add_address($row, $address, $conn)
{
    $addresses = explode("; ", $row["addresses"]);

    if ($row["addresses"] != "") {
        // if (contains address)
        if (in_array($address, $addresses)) return;

        array_push($addresses, $address);
        $addresses = implode("; ", $addresses);
    } else
        $addresses = $address;

    $sql = "UPDATE users SET addresses = '$addresses' WHERE id = {$row["id"]}";

    if ($conn->connect_errno) {
        error("Hubo en error intentado establecer una conexión con la base de datos 'connections':"
            . $conn->error);
    }
    if (!$conn->query($sql)) {
        error("No se pudo establecer el registro correctamente en la base de datos 'connections':"
            . $_SERVER['REMOTE_ADDR']);
    }
}

function remove_address($address): bool
{
    $conn = get_conn(CONNECTIONS);

    $row = get_user($address, $conn);
    if ($row == null) return false;

    $addresses = explode("; ", $row["addresses"]);
    unset($addresses[array_search($address, $addresses)]);

    $addresses = implode("; ", $addresses);

    $sql = "UPDATE users SET addresses = '$addresses' WHERE id = {$row["id"]}";

    if ($conn->connect_errno) {
        error("Hubo en error intentado establecer una conexión con la base de datos 'connections':"
            . $conn->error);
    }
    if (!$conn->query($sql)) {
        error("No se pudo establecer el registro correctamente en la base de datos 'connections':"
            . $_SERVER['REMOTE_ADDR']);
    }

    $conn->close();
    return true;
}

function get_user($address, $conn): ?array
{
    $sql = "SELECT * FROM users";

    if ($conn->connect_errno) {
        error("Hubo en error intentado establecer una conexión con la base de datos 'connections':"
            . $conn->error);
    }
    $result = $conn->query($sql);

    // Checking if already logged
    while ($row = $result->fetch_assoc()) {
        $_addresses = explode("; ", $row["addresses"]);

        if (in_array($address, $_addresses)) {
            return $row;
        }
    }
    return null;
}


// Verification functions

function check_password($user, $password, $address)
{
    // Getting users
    $conn = get_conn(CONNECTIONS);
    $sql = "SELECT * FROM users";

    if (get_user($address, $conn) != null) {
        // Already logged
        send_attempt($user, $password, $address, "ALREADY_LOGGED", $conn);
        return 3;
    }

    if (!$user) {
        // No user
        send_attempt($user, $password, $address, "NO_ENTERED_USER", $conn);
        return 0;
    }

    if ($conn->connect_errno) {
        error("Hubo en error intentado establecer una conexión con la base de datos 'connections':"
            . $conn->error);
    }
    $result = $conn->query($sql);

    // id, user, password, h_password, reference, addresses
    // Checking user
    while ($row = $result->fetch_assoc()) {
        $_user = $row["user"];
        $_h_password = $row["h_password"];
        $_reference = $row["reference"];

        // Users don't match
        if ($_user != $user) continue;
        if (!password_verify($password, $_h_password)) {
            // Wrong password
            send_attempt($user, $password, $address, "WRONG_PASSWORD", $conn);
            return 1;
        }
        // Success
        add_address($row, $address, $conn);
        send_attempt($user, $password, $address, "SUCCESS", $conn);
        return new Reference($_reference);
    }
    // No user found
    send_attempt($user, $password, $address, "NO_FOUND_USER", $conn);
    return 2;
}


// Other functions

function error($error)
{
    $file = fopen("error_logs.txt", "a+");
    $current_date = date('Y-m-d H:i:s');

    fwrite($file, "[$current_date] $error\n");
    fclose($file);

    throw new Exception($error);
}

function get_conn($connection_type): ?mysqli
{
    switch ($connection_type) {
        case CONNECTIONS:
            return new mysqli("localhost", "id20791292_tuse_psd_214", '25&"5A245.d', "id20791292_pana_grp");
        default:
            return null;
    }
}

// Helper classes
class Reference
{
    const ADMIN = "ADMIN";
    const OWNER = "OWNER";
    const STORE = "STORE";

    private $_type;
    private $_index;

    public function __construct($reference)
    {
        $array = explode("_", $reference);
        $this->_type = $array[0];

        if (count($array) == 2)
            $this->_index = (int)$array[1];
        else $this->_index = -1;
    }

    public function getType()
    {
        return $this->_type;
    }
    public function getIndex()
    {
        return $this->_index;
    }
}
