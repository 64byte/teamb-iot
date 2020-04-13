<?php
namespace Iot\Controller;


use \Firebase\JWT\JWT;
use Slimvc\Core\Controller;
use Iot\Model\UserModel;
use Iot\Model\BoardModel;
use Story\Util;
use Story\SomiMailer;

class APIController extends Controller
{
    /**
     */
    public function actionAuth()
    {
        $resultJson = array();
        $this->getApp()->response->headers->set('Content-Type', 'application/json');

        try {
            if (!isset($this->getConfig()["jwtKey"])) {
                $this->getApp()->status(500);
                throw new \Exception("Fatal Error");
            }

            $jwtKey = $this->getConfig()["jwtKey"];

            if (!isset($_POST['email']) || !isset($_POST['password'])) {
                $this->getApp()->status(400);
                throw new \Exception('Bad Request');
            }

            $lengthOfEmail = strlen($_POST['email']);
            if ($lengthOfEmail < 0 || $lengthOfEmail > 64 || !filter_var($_POST['email'],FILTER_VALIDATE_EMAIL)) {
                $this->getApp()->status(500);
                throw new \Exception('Your email or password is incorrect');
            }

            if (!filter_var($_POST['password'], FILTER_VALIDATE_REGEXP,
                array( 'options' => array("regexp" => "/^.*(?=^.{8,15}$)(?=.*\d)(?=.*[a-zA-Z])(?=.*[!@#$%^&+=]).*$/")))) {
                $this->getApp()->status(500);
                throw new \Exception('Your email or password is incorrect');
            }

            $userModel = new UserModel();
            if (!($result = $userModel->getUserWithEmail($_POST['email'], array ('user_id', 'password', 'active')))) {
                $this->getApp()->status(500);
                throw new \Exception('Your email or password is incorrect');
            }

            if (!password_verify($_POST['password'], $result['password'])) {
                $this->getApp()->status(404);
                throw new \Exception("Your email or password is incorrect");
            }

            if (!$result['active']) {
                $this->getApp()->status(403);
                throw new \Exception("You have to active your email.");
            }

            $tokenId = bin2hex(openssl_random_pseudo_bytes(32));
            $issuedAt = time();
            $notBefore = $issuedAt;
            $expire = $notBefore + 6000;
            $hostName = 'teamb-iot.calit2.net';

            $data = [
                'iat' => $issuedAt,
                'jti' => $tokenId,
                'iss' => $hostName,
                'nbf' => $notBefore,
                'exp' => $expire,
                'http://teamb-iot.calit2.net/is_admin' => false,
                'data' => [
                    'user_id' => $result['user_id']
                ]
            ];

            $this->getApp()->status(201);

            $apiAccessKey = base64_encode(JWT::encode($data, $jwtKey, 'HS512'));
            $resultJson['data'] = array(
                'user_id' => $result['user_id'],
                'clientId' => $apiAccessKey
            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            $resultJson['meta'] = array(
                'error' => true,
                'message' => $e->getMessage()
            );

            if ($this->getApp()->response->Status() === 200)
                $this->getApp()->status(400);

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $this->getApp()->stop();
        }
    }

    public function actionReauth($id)
    {
        $resultJson = array();
        $this->getApp()->response->headers->set('Content-Type', 'application/json');

        try {
            if (!isset($this->getConfig()["jwtKey"])) {
                $this->getApp()->status(500);
                throw new \Exception("Fatal Error");
            }

            $jwtKey = $this->getConfig()["jwtKey"];

            $tokenId = bin2hex(openssl_random_pseudo_bytes(32));
            $issuedAt = time();
            $notBefore = $issuedAt;
            $expire = $notBefore + 6000;
            $hostName = 'teamb-iot.calit2.net';

            $data = [
                'iat' => $issuedAt,
                'jti' => $tokenId,
                'iss' => $hostName,
                'nbf' => $notBefore,
                'exp' => $expire,
                'http://teamb-iot.calit2.net/is_admin' => false,
                'data' => [
                    'user_id' => $id
                ]
            ];

            $this->getApp()->status(201);

            $apiAccessKey = base64_encode(JWT::encode($data, $jwtKey, 'HS512'));

            $resultJson['data'] = array(
                'user_id' => $id,
                'clientId' => $apiAccessKey
            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            $resultJson['meta'] = array(
                'error' => true,
                'message' => $e->getMessage()
            );

            if ($this->getApp()->response->Status() === 200)
                $this->getApp()->status(400);

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $this->getApp()->stop();
        }
    }

    public function actionAuthBlacklist($id)
    {
        $resultJson = array();
        $this->getApp()->response->headers->set('Content-Type', 'application/json');

        try {
            if (!isset($_SESSION['blacklist'][$id]) || !is_array($_SESSION['blacklist'][$id])) {
                $_SESSION['blacklist'][$id] = array();
                array_push($_SESSION['blacklist'][$id], $_GET['clientId']);
            }
            else {
                if (!in_array($_GET['clientId'], $_SESSION['blacklist'][$id]))
                    array_push($_SESSION['blacklist'][$id], $_GET['clientId']);
            }

            var_dump($_SESSION['blacklist']);
            $this->getApp()->status(200);

            $resultJson['data'] = array(

            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            $resultJson['meta'] = array(
                'error' => true,
                'message' => $e->getMessage()
            );

            if ($this->getApp()->response->Status() === 200)
                $this->getApp()->status(400);

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $this->getApp()->stop();
        }
    }

    public function actionGetUsers()
    {

    }

    public function actionGetUser($id)
    {
        $resultJson = array();
        $this->getApp()->response->headers->set('Content-Type', 'application/json');

        try {
            $fieldsStr = $this->getApp()->request()->get('fields', '');

            $fields = explode(',', $fieldsStr);
            foreach ($fields as $key => $field) {
                $field = trim($field);
                if (!$field) {
                    unset($fields[$key]);
                }
            }

            $userModel = new UserModel();
            if (!$result = $userModel->getUser($id, $fields)) {
                $this->getApp()->status(404);
                $result = array();
            }

            $resultJson['data'] = array(
                'users' => $result
            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            if ($this->getApp()->response->Status() === 200)
                $this->getApp()->status(400);

            $resultJson['meta'] = array(
                'error' => true,
                'message' => $e->getMessage()
            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $this->getApp()->stop();
        }
    }

    public function actionUpdateUserPassword($id)
    {
        $resultJson = array();
        $this->getApp()->response->headers->set('Content-Type', 'application/json');

        try {
            $password = $this->getApp()->request()->put('password');
            $newPassword = $this->getApp()->request()->put('new_password');

            /*            $lengthOfPassword = strlen($password);
                        if (!isset($password) || $lengthOfPassword < 8 || $lengthOfPassword > 15) {
                            $this->getApp()->status(400);
                            throw new \Exception("Your current password is incorrect");
                        }

                        $lengthOfNewPassword = strlen($newPassword);
                        if (!isset($newPassword) || $lengthOfNewPassword < 8 || $lengthOfNewPassword > 15) {
                            $this->getApp()->status(400);
                            throw new \Exception("Your new password is incorrect");
                        } */

            $userModel = new UserModel();
            if (!$result = $userModel->getUser($id)) {
                $this->getApp()->status(500);
                throw new \Exception("Internal Error");
            }

            if (!password_verify($password, $result['password'])) {
                $this->getApp()->status(400);
                throw new \Exception("Your current password is incorrect");
            }

            if (!$resultCd = $userModel->updateUserPassword($id, password_hash($newPassword, PASSWORD_DEFAULT))) {
                $this->getApp()->status(500);
                throw new \Exception("Internal Error");
            }

            $resultJson['data'] = array(
//                'users' => $result
            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            if ($this->getApp()->response->Status() === 200)
                $this->getApp()->status(400);

            $resultJson['meta'] = array(
                'error' => true,
                'message' => $e->getMessage()
            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $this->getApp()->stop();
        }
    }

    public function actionDeleteUser($id)
    {
        $resultJson = array();
        $this->getApp()->response->headers->set('Content-Type', 'application/json');

        try {
            $password = $this->getApp()->request()->delete('password');

            $lengthOfPassword = strlen($password);
            if (!isset($password) || $lengthOfPassword < 8 || $lengthOfPassword > 15) {
                $this->getApp()->status(400);
                throw new \Exception("Password is incorrect");
            }

            $userModel = new UserModel();
            if (!$result = $userModel->getUser($id)) {
                $this->getApp()->status(500);
                throw new \Exception("Bad Request");
            }

            if (!password_verify($password, $result['password'])) {
                $this->getApp()->status(400);
                throw new \Exception("Password is incorrect");
            }

            if (!$resultCd = $userModel->deleteUser($id)) {
                $this->getApp()->status(500);
                throw new \Exception("Internal Error");
            }

            /*
            if (!isset($_SESSION['blacklist'][$id]) || !is_array($_SESSION['blacklist'][$id])) {
                $_SESSION['blacklist'][$id] = array();
                array_push($_SESSION['blacklist'][$id], $_GET['clientId']);
            }
            else {
                if (!in_array($_GET['clientId'], $_SESSION['blacklist'][$id]))
                    array_push($_SESSION['blacklist'][$id], $_GET['clientId']);
            }
            */

            $resultJson['data'] = array(
//                'users' => $result
            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            if ($this->getApp()->response->Status() === 200)
                $this->getApp()->status(400);

            $resultJson['meta'] = array(
                'error' => true,
                'message' => $e->getMessage()
            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $this->getApp()->stop();
        }
    }

    public function actionInsertBoardOfUser($id)
    {
        $resultJson = array();
        $this->getApp()->response->headers->set('Content-Type', 'application/json');

        try {
            if (!isset($_POST['mac_address']) || !isset($_POST['name'])) {
                throw new \Exception("you have to input all data.");
            }

            $lengthOfMacAddr = strlen($_POST['mac_address']);
            if ($lengthOfMacAddr < 0 || $lengthOfMacAddr > 17) {
                throw new \Exception("Bad Request");
            }

            $lengthOfName = strlen($_POST['name']);
            if ($lengthOfName < 0 || $lengthOfName > 50) {
                throw new \Exception("Bad Request");
            }

            $userModel = new UserModel();
            if (!$result = $userModel->insertBoardOfUser($id, $_POST['mac_address'], $_POST['name'])) {
                $this->getApp()->status(500);
                throw new \Exception("Internal Error");
            }

            $resultJson['data'] = array(
                'board_id' => $result
            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            if ($this->getApp()->response->Status() === 200)
                $this->getApp()->status(400);

            $resultJson['meta'] = array(
                'error' => true,
                'message' => $e->getMessage()
            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $this->getApp()->stop();
        }
    }

    public function actionGetAllBoardsOfUser($id)
    {
        $resultJson = array();
        $this->getApp()->response->headers->set('Content-Type', 'application/json');

        try {
            $fieldsStr = $this->getApp()->request()->get('fields', '');
            $isCsv = ($this->getApp()->request()->get('csv', false) === 'true');
            $offset = intval($this->getApp()->request()->get('offset'));
            $limit = intval($this->getApp()->request()->get('limit'));
            $orderby = ($this->getApp()->request()->get('orderby', false) === 'true');

            $fields = explode(',', $fieldsStr);
            foreach ($fields as $key => $field) {
                $field = trim($field);
                if (!$field) {
                    unset($fields[$key]);
                }
            }

            $userModel = new UserModel();
            if (!$result = $userModel->getAllBoardsOfUser($id, $fields, $offset, $limit, $orderby)) {
                $this->getApp()->status(404);
                $result = array();
            }

            $resultJson['data'] = array(
                'boards' => $result
            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            if ($this->getApp()->response->Status() === 200)
                $this->getApp()->status(400);

            $resultJson['meta'] = array(
                'error' => true,
                'message' => $e->getMessage()
            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $this->getApp()->stop();
        }
    }

    public function actionGetAllHRDataOfUser($id)
    {
        $resultJson = array();
        $this->getApp()->response->headers->set('Content-Type', 'application/json');

        try {
            $fieldsStr = $this->getApp()->request()->get('fields', '');
            $isCsv = ($this->getApp()->request()->get('csv', false) === 'true');
            $offset = intval($this->getApp()->request()->get('offset'));
            $limit = intval($this->getApp()->request()->get('limit'));
            $orderby = ($this->getApp()->request()->get('orderby', false) === 'true');
            $startDate = intval($this->getApp()->request()->get('startDate'));
            $endDate = intval($this->getApp()->request()->get('endDate'));


            $fields = explode(',', $fieldsStr);
            foreach ($fields as $key => $field) {
                $field = trim($field);
                if (!$field) {
                    unset($fields[$key]);
                }
            }

            $userModel = new UserModel();
            if ($isCsv === true) {
                if (!$result = $userModel->getAllHRDataOfUserCSV($id, $offset, $limit, $startDate, $endDate, $orderby)) {
                    $result = array();
                }

//                $this->getApp()->response->headers->set('Content-Type', 'text/csv');
            } else {
                if (!$result = $userModel->getAllHRDataOfUser($id, $fields, $offset, $limit, $startDate, $endDate, $orderby)) {
                    $result = array();
                }

                $resultJson['data'] = array(
                    'hrdata' => $result
                );

                echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            }
        } catch (\Exception $e) {
            if ($this->getApp()->response->Status() === 200)
                $this->getApp()->status(400);

            $resultJson['meta'] = array(
                'error' => true,
                'message' => $e->getMessage()
            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $this->getApp()->stop();
        }
    }

    public function actionInsertHRDataOfUser($id)
    {
        $resultJson = array();
        $this->getApp()->response->headers->set('Content-Type', 'application/json');

        try {
            $data = array(
                'heartrate' => '',
                'rr_interval' => '',
                'latitude' => '',
                'longitude' => '',
                'hrdata_timestamp' => ''
            );

            foreach ($data as $key => $field) {
                if (!isset($_POST[$key])) {
                    $this->getApp()->status(400);
                    throw new \Exception('You have to input all data.');
                }

                $data[$key] = $_POST[$key];
            }

            $userModel = new UserModel();
            if (!($result = $userModel->insertHRDataOfUser($id, $data))) {
                $this->getApp()->status(404);
                $result = array();
            }

            $resultJson['data'] = array(
                'hrdata_id' => $result
            );

            $this->getApp()->status(201);
            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            if ($this->getApp()->response->Status() === 200)
                $this->getApp()->status(400);

            $resultJson['meta'] = array(
                'error' => true,
                'message' => $e->getMessage()
            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $this->getApp()->stop();
        }
    }

    public function actionInsertHRDataOfUsersCSV()
    {
        $resultJson = array();
        $this->getApp()->response->headers->set('Content-Type', 'application/json');

        try {
            $fp = fopen("php://temp", "r+");
            fputs($fp, htmlspecialchars($this->getApp()->request->getBody()));
            rewind($fp);

            $result = null;
            $boardModel = new BoardModel();
            while (($data = fgetcsv($fp, 1024, ",")) !== FALSE) {
                if (!($result = $boardModel->insertHRDataOfUserRow($data))) {
                    $this->getApp()->status(500);
                    throw new \Exception("Internal Error");
                }
            }
            fclose($fp);

            $resultJson['data'] = array(
                'hrdata_id' => $result
            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            if ($this->getApp()->response->Status() === 200)
                $this->getApp()->status(400);

            $resultJson['meta'] = array(
                'error' => true,
                'message' => $e->getMessage()
            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $this->getApp()->stop();
        }
    }

    public function actionGetAPDataOfAllBoardsOfUser($id)
    {
        $resultJson = array();
        $this->getApp()->response->headers->set('Content-Type', 'application/json');

        try {
            $fieldsStr = $this->getApp()->request()->get('fields', '');
            $isCsv = ($this->getApp()->request()->get('csv', false) === 'true');
            $offset = intval($this->getApp()->request()->get('offset'));
            $limit = intval($this->getApp()->request()->get('limit'));
            $orderby = ($this->getApp()->request()->get('orderby', false) === 'true');
            $startDate = intval($this->getApp()->request()->get('startDate'));
            $endDate = intval($this->getApp()->request()->get('endDate'));
            $lat = floatval($this->getApp()->request()->get('lat'));
            $lng = floatval($this->getApp()->request()->get('lng'));

            $fields = explode(',', $fieldsStr);
            foreach ($fields as $key => $field) {
                $field = trim($field);
                if (!$field) {
                    unset($fields[$key]);
                }
            }

            $userModel = new UserModel();
            if ($isCsv) {
                if (!$result = $userModel->getAPDataOfAllBoardsOfUserCSV($id, $offset, $limit, $startDate, $endDate, $lat, $lng, $orderby)) {
                    $result = array();
                }

//                $this->getApp()->response->headers->set('Content-Type', 'text/csv');
            } else {
                if (!$result = $userModel->getAPDataOfAllBoardsOfUser($id, $fields, $offset, $limit, $startDate, $endDate, $lat, $lng, $orderby)) {
                    $this->getApp()->status(404);
                    $result = array();
                }

                $resultJson['data'] = array(
                    'apdata' => $result
                );

                echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            }
        } catch (\Exception $e) {
            if ($this->getApp()->response->Status() === 200)
                $this->getApp()->status(400);

            $resultJson['meta'] = array(
                'error' => true,
                'message' => $e->getMessage()
            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $this->getApp()->stop();
        }
    }

    public function actionGetAPDataOfBoardsOfUser($id, $bid)
    {
        $resultJson = array();
        $this->getApp()->response->headers->set('Content-Type', 'application/json');

        try {
            $fieldsStr = $this->getApp()->request()->get('fields', '');
            $isCsv = ($this->getApp()->request()->get('csv', false) === 'true');
            $offset = intval($this->getApp()->request()->get('offset'));
            $limit = intval($this->getApp()->request()->get('limit'));
            $orderby = ($this->getApp()->request()->get('orderby', false) === 'true');
            $startDate = intval($this->getApp()->request()->get('startDate'));
            $endDate = intval($this->getApp()->request()->get('endDate'));
            $lat = floatval($this->getApp()->request()->get('lat'));
            $lng = floatval($this->getApp()->request()->get('lng'));

            $fields = explode(',', $fieldsStr);
            foreach ($fields as $key => $field) {
                $field = trim($field);
                if (!$field) {
                    unset($fields[$key]);
                }
            }

            $userModel = new UserModel();

            if ($isCsv) {
                if (!$result = $userModel->getAPDataOfBoardsOfUserCSV($id, $bid, $offset, $limit, $startDate, $endDate, $lat, $lng, $orderby)) {
                    $result = array();
                }

                header('Content-Type: text/csv');
            } else {

                if (!$result = $userModel->getAPDataOfBoardsOfUser($id, $bid, $offset, $limit, $startDate, $endDate, $lat, $lng, $fields)) {
                    $this->getApp()->status(404);
                    $result = array();
                }

                $resultJson['data'] = array(
                    'apdata' => $result
                );

                echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            }
        } catch (\Exception $e) {
            if ($this->getApp()->response->Status() === 200)
                $this->getApp()->status(400);

            $resultJson['meta'] = array(
                'error' => true,
                'message' => $e->getMessage()
            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $this->getApp()->stop();
        }
    }

    public function actionInsertAPDataOfBoardOfUser($id, $bid)
    {
        $resultJson = array();
        $this->getApp()->response->headers->set('Content-Type', 'application/json');

        try {
            $data = array(
                'no2' => '',
                'o3' => '',
                'co' => '',
                'so2' => '',
                'pm25' => '',
                'temperature' => '',
                'latitude' => '',
                'longitude' => '',
                'apdata_timestamp' => '',
            );

            foreach ($data as $key => $field) {
                if (!isset($_POST[$key])) {
                    $this->getApp()->status(400);
                    throw new \Exception('Bad request');
                }

                $data[$key] = $_POST[$key];
            }

            $userModel = new UserModel();
            if (!($result = $userModel->insertAPDataOfBoardOfUser($id, $bid, $data))) {
                $this->getApp()->status(500);
                $result = array();
            }

            $resultJson['data'] = array(
                'apdata_id' => $result
            );

            $this->getApp()->status(201);
            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            if ($this->getApp()->response->Status() === 200)
                $this->getApp()->status(400);

            $resultJson['meta'] = array(
                'error' => true,
                'message' => $e->getMessage()
            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $this->getApp()->stop();
        }
    }

    public function actionInsertAPDataOfBoardsCSV()
    {
        $resultJson = array();
        $this->getApp()->response->headers->set('Content-Type', 'application/json');

        try {
            $fp = fopen("php://temp", "r+");
            fputs($fp, htmlspecialchars($this->getApp()->request->getBody()));
            rewind($fp);

            $result = null;
            $boardModel = new BoardModel();
            while (($data = fgetcsv($fp, 1024, ",")) !== FALSE) {
                if (!($result = $boardModel->insertAPDataOfBoardWithRow($data))) {
                    $this->getApp()->status(500);
                    throw new \Exception("Internal Error");
                }
            }
            fclose($fp);

            $resultJson['data'] = array(
                'apdata_id' => $result
            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            if ($this->getApp()->response->Status() === 200)
                $this->getApp()->status(400);

            $resultJson['meta'] = array(
                'error' => true,
                'message' => $e->getMessage()
            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $this->getApp()->stop();
        }
    }

    public function actionGetAQIDataOfAllBoardsOfUser($id)
    {
        $resultJson = array();
        $this->getApp()->response->headers->set('Content-Type', 'application/json');

        try {
            $fieldsStr = $this->getApp()->request()->get('fields', '');
            $isCsv = ($this->getApp()->request()->get('csv', false) === 'true');
            $offset = intval($this->getApp()->request()->get('offset'));
            $limit = intval($this->getApp()->request()->get('limit'));
            $orderby = ($this->getApp()->request()->get('orderby', false) === 'true');
            $startDate = intval($this->getApp()->request()->get('startDate'));
            $endDate = intval($this->getApp()->request()->get('endDate'));
            $lat = floatval($this->getApp()->request()->get('lat'));
            $lng = floatval($this->getApp()->request()->get('lng'));

            $fields = explode(',', $fieldsStr);
            foreach ($fields as $key => $field) {
                $field = trim($field);
                if (!$field) {
                    unset($fields[$key]);
                }
            }

            $userModel = new UserModel();
            if ($isCsv) {
                if (!$result = $userModel->getAQIDataOfAllBoardsOfUserCSV($id, $offset, $limit, $startDate, $endDate, $lat, $lng, $orderby)) {
                    $result = array();
                }

//                $this->getApp()->response->headers->set('Content-Type', 'text/csv');
            } else {
                if (!$result = $userModel->getAQIDataOfAllBoardsOfUser($id, $fields, $offset, $limit, $startDate, $endDate, $lat, $lng, $orderby)) {
                    $this->getApp()->status(404);
                    $result = array();
                }

                $resultJson['data'] = array(
                    'aqidata' => $result
                );

                echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            }
        } catch (\Exception $e) {
            if ($this->getApp()->response->Status() === 200)
                $this->getApp()->status(400);

            $resultJson['meta'] = array(
                'error' => true,
                'message' => $e->getMessage()
            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $this->getApp()->stop();
        }
    }

    public function actionGetAQIDataOfBoardsOfUser($id, $bid)
    {
        $resultJson = array();
        $this->getApp()->response->headers->set('Content-Type', 'application/json');

        try {
            $fieldsStr = $this->getApp()->request()->get('fields', '');
            $isCsv = ($this->getApp()->request()->get('csv', false) === 'true');
            $offset = intval($this->getApp()->request()->get('offset'));
            $limit = intval($this->getApp()->request()->get('limit'));
            $orderby = ($this->getApp()->request()->get('orderby', false) === 'true');
            $startDate = intval($this->getApp()->request()->get('startDate'));
            $endDate = intval($this->getApp()->request()->get('endDate'));
            $lat = floatval($this->getApp()->request()->get('lat'));
            $lng = floatval($this->getApp()->request()->get('lng'));

            $fields = explode(',', $fieldsStr);
            foreach ($fields as $key => $field) {
                $field = trim($field);
                if (!$field) {
                    unset($fields[$key]);
                }
            }

            $userModel = new UserModel();

            if ($isCsv) {
                if (!$result = $userModel->getAQIDataOfBoardsOfUserCSV($id, $bid, $offset, $limit, $startDate, $endDate, $lat, $lng, $orderby)) {
                    $result = array();
                }

//                header('Content-Type: text/csv');
            } else {

                if (!$result = $userModel->getAQIDataOfBoardsOfUser($id, $bid, $fields, $offset, $limit, $startDate, $endDate, $lat, $lng, $orderby)) {
                    $this->getApp()->status(404);
                    $result = array();
                }

                $resultJson['data'] = array(
                    'aqidata' => $result
                );

                echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            }
        } catch (\Exception $e) {
            if ($this->getApp()->response->Status() === 200)
                $this->getApp()->status(400);

            $resultJson['meta'] = array(
                'error' => true,
                'message' => $e->getMessage()
            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $this->getApp()->stop();
        }
    }

    public function actionGetAQIData()
    {
        $resultJson = array();
        $this->getApp()->response->headers->set('Content-Type', 'application/json');

        try {
            $fieldsStr = $this->getApp()->request()->get('fields', '');
            $isCsv = ($this->getApp()->request()->get('csv', false) === 'true');
            $offset = intval($this->getApp()->request()->get('offset'));
            $limit = intval($this->getApp()->request()->get('limit'));
            $interlt = intval($this->getApp()->request()->get('interlt'));
            $orderby = ($this->getApp()->request()->get('orderby', false) === 'true');
            $startDate = intval($this->getApp()->request()->get('startDate'));
            $endDate = intval($this->getApp()->request()->get('endDate'));
            $lat = floatval($this->getApp()->request()->get('lat'));
            $lng = floatval($this->getApp()->request()->get('lng'));

            $distinct = ($this->getApp()->request()->get('dist', false));
            $distincloc = ($this->getApp()->request()->get('disloc', false));


            $fields = explode(',', $fieldsStr);
            foreach ($fields as $key => $field) {
                $field = trim($field);
                if (!$field) {
                    unset($fields[$key]);
                }
            }

            $boardModel = new BoardModel();
            if ($isCsv === true) {
                if ($distinct) {
                    if (!$result = $boardModel->getDistinctAQIDataCSV($offset, $limit, $orderby)) {
                        $result = array();
                    }
                } else {
                    if ($distincloc) {
                        if (!$result = $boardModel->getDistinctLatLngAQIDataCSV($offset, $limit, $startDate, $endDate, $orderby)) {
                            $result = array();
                        }
                    } else if (!$result = $boardModel->getAQIDataCSV($offset, $limit, $interlt, $startDate, $endDate, $lat, $lng, $orderby)) {
                        $result = array();
                    }
                }

              //  var_dump($result);

  //              $this->getApp()->response->headers->set('Content-Type', 'text/csv');
//                $this->getApp()->response->headers->set('Content-Disposition', 'attachment; filename="'.$result.'"');
            } else {
                if (!$result = $boardModel->getAQIData($fields, $offset, $limit, $interlt, $startDate, $endDate, $lat, $lng, $orderby)) {
                    $result = array();
                }

                $resultJson['data'] = array(
                    'aqidata' => $result
                );

                echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            }
        } catch (\Exception $e) {
            if ($this->getApp()->response->Status() === 200)
                $this->getApp()->status(400);

            $resultJson['meta'] = array(
                'error' => true,
                'message' => $e->getMessage()
            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $this->getApp()->stop();
        }
    }

    public function actionGetDistincAQIData()
    {
        $resultJson = array();
        $this->getApp()->response->headers->set('Content-Type', 'application/json');

        try {
            $fieldsStr = $this->getApp()->request()->get('fields', '');
            $isCsv = ($this->getApp()->request()->get('csv', false) === 'true');
            $offset = intval($this->getApp()->request()->get('offset'));
            $limit = intval($this->getApp()->request()->get('limit'));
            $orderby = ($this->getApp()->request()->get('orderby', false) === 'true');


            $fields = explode(',', $fieldsStr);
            foreach ($fields as $key => $field) {
                $field = trim($field);
                if (!$field) {
                    unset($fields[$key]);
                }
            }

            $boardModel = new BoardModel();
            if ($isCsv === true) {
                if (!$result = $boardModel->getDistinctAQIDataCSV($offset, $limit, $orderby)) {
                    $result = array();
                }

            } else {
/*                if (!$result = $boardModel->getAQIData($fields, $offset, $limit, $interlt, $startDate, $endDate, $lat, $lng, $orderby)) {
                    $result = array();
                }

                $resultJson['data'] = array(
                    'aqidata' => $result
                );

                echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); */
            }
        } catch (\Exception $e) {
            if ($this->getApp()->response->Status() === 200)
                $this->getApp()->status(400);

            $resultJson['meta'] = array(
                'error' => true,
                'message' => $e->getMessage()
            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $this->getApp()->stop();
        }
    }

    public function actionInsertAQIDataOfBoardOfUser($id, $bid)
    {
        $resultJson = array();
        $this->getApp()->response->headers->set('Content-Type', 'application/json');

        try {
            $data = array(
                'no2' => '',
                'o3' => '',
                'co' => '',
                'so2' => '',
                'pm25' => '',
                'latitude' => '',
                'longitude' => '',
                'aqidata_timestamp' => '',
            );

            foreach ($data as $key => $field) {
                if (!isset($_POST[$key])) {
                    $this->getApp()->status(400);
                    throw new \Exception('Bad request');
                }

                $data[$key] = $_POST[$key];
            }

            $userModel = new UserModel();
            if (!($result = $userModel->insertAQIDataOfBoardOfUser($id, $bid, $data))) {
                $this->getApp()->status(500);
                $result = array();
            }

            $resultJson['data'] = array(
                'aqidata_id' => $result
            );

            $this->getApp()->status(201);
            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            if ($this->getApp()->response->Status() === 200)
                $this->getApp()->status(400);

            $resultJson['meta'] = array(
                'error' => true,
                'message' => $e->getMessage()
            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $this->getApp()->stop();
        }
    }

    public function actionInsertAQIDataOfBoardsCSV()
    {
        $resultJson = array();
        $this->getApp()->response->headers->set('Content-Type', 'application/json');

        try {
            $fp = fopen("php://temp", "r+");
            fputs($fp, htmlspecialchars($this->getApp()->request->getBody()));
            rewind($fp);

            $result = null;
            $boardModel = new BoardModel();
            while (($data = fgetcsv($fp, 1024, ",")) !== FALSE) {
                if (!($result = $boardModel->insertAQIDataOfBoardWithRow($data))) {
                    $this->getApp()->status(500);
                    throw new \Exception("Internal Error");
                }
            }
            fclose($fp);

            $resultJson['data'] = array(
                'aqidata_id' => $result
            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            if ($this->getApp()->response->Status() === 200)
                $this->getApp()->status(400);

            $resultJson['meta'] = array(
                'error' => true,
                'message' => $e->getMessage()
            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $this->getApp()->stop();
        }
    }

    public function actionGetBoardAQIData($bid)
    {
        $resultJson = array();
        $this->getApp()->response->headers->set('Content-Type', 'application/json');

        try {
            $fieldsStr = $this->getApp()->request()->get('fields', '');
            $isCsv = ($this->getApp()->request()->get('csv', false) === 'true');
            $offset = intval($this->getApp()->request()->get('offset'));
            $limit = intval($this->getApp()->request()->get('limit'));
            $interlt = intval($this->getApp()->request()->get('interlt'));
            $orderby = ($this->getApp()->request()->get('orderby', false) === 'true');
            $startDate = intval($this->getApp()->request()->get('startDate'));
            $endDate = intval($this->getApp()->request()->get('endDate'));
            $lat = floatval($this->getApp()->request()->get('lat'));
            $lng = floatval($this->getApp()->request()->get('lng'));


            $fields = explode(',', $fieldsStr);
            foreach ($fields as $key => $field) {
                $field = trim($field);
                if (!$field) {
                    unset($fields[$key]);
                }
            }

            $boardModel = new BoardModel();
            if ($isCsv === true) {
                if (!$result = $boardModel->getBoardAQIDataCSV($bid, $offset, $limit, $interlt, $startDate, $endDate, $lat, $lng, $orderby)) {
                    $result = array();
                }

                //  var_dump($result);

                //              $this->getApp()->response->headers->set('Content-Type', 'text/csv');
//                $this->getApp()->response->headers->set('Content-Disposition', 'attachment; filename="'.$result.'"');
            } else {
                if (!$result = $boardModel->getBoardAQIData($bid, $fields, $offset, $limit, $interlt, $startDate, $endDate, $lat, $lng, $orderby)) {
                    $result = array();
                }

                $resultJson['data'] = array(
                    'aqidata' => $result
                );

                echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            }
        } catch (\Exception $e) {
            if ($this->getApp()->response->Status() === 200)
                $this->getApp()->status(400);

            $resultJson['meta'] = array(
                'error' => true,
                'message' => $e->getMessage()
            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $this->getApp()->stop();
        }
    }

    public function actionForgotPassword()
    {
        $resultJson = array();
        $this->getApp()->response->headers->set('Content-Type', 'application/json');

        try {
            $email = $_POST['email'];
            if (strlen($email) > 64 || !filter_var($email,FILTER_VALIDATE_EMAIL)) {
                $this->getApp()->status(500);
                throw new \Exception();
            }

            $userModel = new UserModel();
            if (!$userModel->getUserWithEmail($email, array ('user_id'))) {
                $this->getApp()->status(500);
                throw new \Exception();
            }

            if (!$userModel->updateUserPwdActiveWithEmail($email, 1)) {
                $this->getApp()->status(500);
                throw new \Exception();
            }

            $mailer = new SomiMailer();

            $tokenId = bin2hex(openssl_random_pseudo_bytes(32));
            $satKey = $this->getConfig()['ptpKey'];
            $verification = array(
                'tki' => $tokenId,
                'exp' => time() + 600,
                'email' => $email,
            );

            $veriData = Util::urlsafeB64Encode(json_encode($verification));
            $signature = Util::urlsafeB64Encode(hash_hmac('sha256', Util::urlsafeB64Encode(json_encode($verification)), $satKey));
            $resultCode = Util::urlsafeB64Encode($veriData.'.'.$signature);
            $confirmURL = 'http://teamb-iot.calit2.net/forgotpwd/confirm?v='.$resultCode;
            $mailMsg = '<p>Please check the below link</p>'.'<a href="'.$confirmURL.'">Click the link to get your temporary password</a>';

            $mailer->sendMail($email, 'Please confirm to get your temporary password', $mailMsg);

            $resultJson['data'] = array();
            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            $resultJson['meta'] = array(
                'error' => true,
                'message' => 'Confirm mail have been sent. Check your mail box.'
            );

            if ($this->getApp()->response->Status() === 200)
                $this->getApp()->status(400);

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $this->getApp()->stop();
        }
    }

    public function actionChangePassword($id)
    {
        if(!isset($_POST['password']) || !isset($_POST['new_password'])) {
            $this->getApp()->flash('alert', 'Your password is incorrect');
            $this->getApp()->redirect('/profile/password');
        }

        /*
        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            $this->getApp()->flash('alert', 'Your password is bad');
            $this->getApp()->redirect('/profile/password');
        }
        */

        $password = $_POST['password'];
        $newPassword = $_POST['new_password'];
        // password check

        $userModel = new UserModel();
        if (!$result = $userModel->getUser($_SESSION['user_id'])) {
            return;
        }

        if (!password_verify($password, $result['password'])) {
            $this->getApp()->flash('alert', 'Your password is incorrect');
            $this->getApp()->redirect('/profile/password');
        }

        if (!$userModel->updateUserPassword($result['user_id'], password_hash($newPassword, PASSWORD_DEFAULT))) {
            $this->getApp()->flash('alert', 'Internal Error');
            $this->getApp()->redirect('/profile/password');
        }

        unset($_SESSION['user_id']);
        $this->getApp()->redirect('/');
    }
}
