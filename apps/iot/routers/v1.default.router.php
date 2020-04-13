<?php

use \Firebase\JWT\JWT;
use Iot\Middleware\ApiAuthMiddleware;
use Story\Util;

$app->get('/test', 'Iot\Controller\IndexController:actionSecond')
    ->name('get-homepage2');

/* MAIN PAGE --------------------------------------------------------------------- */
$app->get('/', 'Iot\Controller\IndexController:actionIndex')
    ->name('get-homepage');

$app->get('/profile', 'Iot\Controller\IndexController:actionProfile')
    ->name('get-profile-homepage');

$app->get('/boards', 'Iot\Controller\IndexController:actionBoards')
    ->name('get-boards-homepage');

$app->get('/profile/password', 'Iot\Controller\IndexController:actionPassword')
    ->name('get-password-homepage');

$app->post('/profile/password', 'Iot\Controller\IndexController:actionPasswordConfirm')
    ->name('post-password-homepage');
/* ------------------------------------------------------------------------------- */

/* USER MANAGEMENT --------------------------------------------------------------- */
$app->get('/signup', 'Iot\Controller\UserController:actionSignUp')
    ->name('get-signup-homepage');

$app->post('/signup', 'Iot\Controller\UserController:actionRequestSignUpForWeb')
    ->name('post-signup-homepage');

$app->get('/signup/confirm', 'Iot\Controller\UserController:actionSignUpConfirm')
    ->name('post-signup-confirm-homepage');

$app->get('/signin', 'Iot\Controller\UserController:actionSignIn')
    ->name('get-signin-homepage');

$app->post('/signin', 'Iot\Controller\UserController:actionRequestSignIn')
    ->name('post-signin-homepage');

$app->get('/reactive', 'Iot\Controller\UserController:actionRerequestSignUp')
    ->name('get-reactive-homepage');

$app->post('/signout', 'Iot\Controller\UserController:actionRequestSignOut')
    ->name('post-signout-homepage');

$app->get('/forgotpwd', 'Iot\Controller\UserController:actionForgotPwd')
    ->name('get-forgotpwd-homepage');

$app->post('/forgotpwd', 'Iot\Controller\UserController:actionRequestForgotPwd')
    ->name('get-forgotpwd-homepage');

$app->get('/forgotpwd/confirm', 'Iot\Controller\UserController:actionForgotPwdConfirm')
    ->name('post-forgotpwd-confirm-homepage');
/* ------------------------------------------------------------------------------- */

/* Data Measurement Function ----------------------------------------------------- */
$app->get('/aqimaps', 'Iot\Controller\IndexController:actionAQIMaps')
    ->name('get-aqimaps-homepage');

$app->get('/historyaqi', 'Iot\Controller\IndexController:actionHistoricalAQIMap')
    ->name('get-aqichart-homepage');

$app->get('/aqichart', 'Iot\Controller\IndexController:actionAQIChart')
    ->name('get-aqichart-homepage');

$app->get('/realtimeap/:id', 'Iot\Controller\IndexController:actionRealTimeAirPollution')
    ->conditions(array('id' => '\d+'))
    ->name('get-realtime-airpollution-homepage');

$app->get('/paqichart/:id', 'Iot\Controller\IndexController:actionPrivateHistoricalAQIChart')
    ->conditions(array('id' => '\d+'))
    ->name('get-private-aqichart-homepage');

$app->get('/historyap/:id', 'Iot\Controller\IndexController:actionHistoricalAirPollution')
    ->conditions(array('id' => '\d+'))
    ->name('get-historical-airpollution-homepage');

$app->get('/historyapm/:id', 'Iot\Controller\IndexController:actionHistoricalAirPollutionMap')
    ->conditions(array('id' => '\d+'))
    ->name('get-historical-airpollution-maps-homepage');

$app->get('/realtimehr', 'Iot\Controller\IndexController:actionRealTimeHeartRate')
    ->name('get-realtime-heartrate-homepage');

$app->get('/historyhr', 'Iot\Controller\IndexController:actionHistoricalHeartRate')
    ->name('get-historical-heartrate-homepage');

$app->get('/historychart', 'Iot\Controller\IndexController:actionHistoryChart')
    ->name('get-historychart-homepage');

$app->get('/historymap', 'Iot\Controller\IndexController:actionHistoryMap')
    ->name('get-historymap-homepage');

$app->get('/historyhrm', 'Iot\Controller\IndexController:actionHistoryHeartRateMap')
    ->name('get-history-heartrate-map-homepage');
/* ------------------------------------------------------------------------------- */

$app->group('/api', function () use ($app) {
    // Middleware For Restful API
    $authenticateForToken = function () {
        $app = \Slim\Slim::getInstance("default");
        session_start();

        try {
            $params = $app->router()->getCurrentRoute()->getParams();

            if (isset($_SESSION['user_id'])) {
                if ($params['id'] !== $_SESSION['user_id']) {
                    $app->status(403);
                    throw new \Exception('Can\'t access the user data');
                }

                return;
            }

            if (!isset($app->container["settings"]) || !isset($app->container["settings"]["jwtKey"])) {
                $app->status(500);
                throw new Exception("Fatal Error");
            }

            $jwtKey = $app->container["settings"]["jwtKey"];
            if (!isset($_GET['clientId'])) {
                $app->status(401);
                throw new \Exception('Can\'t access the user data');
            }

            $result = JWT::decode(base64_decode($_GET['clientId']), $jwtKey, array('HS512'));
            $data = Util::objectToArray($result);

            if (!isset($data['http://teamb-iot.calit2.net/is_admin'])) {
                $app->status(401);
                throw new \Exception('Can\'t access the user data');
            }

            if ($data['http://teamb-iot.calit2.net/is_admin'] === true) {
                return;
            }

            if (!isset($data['data']) || !isset($data['data']['user_id'])) {
                $app->status(401);
                throw new \Exception('Can\'t access the user data');
            }

            if ($params['id'] !== $data['data']['user_id']) {
                $app->status(403);
                throw new \Exception('Can\'t access the user data');
            }

            // pass;
        } catch (\Exception $e) {
            $responseData = array(
                'meta' => array(
                    'error' => true,
                    'message' => $e->getMessage()
                )
            );

            if ($app->response->Status() === 200)
                $app->response->setStatus(401);

            $app->response->headers->set('Content-Type', 'application/json');

            echo json_encode($responseData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $app->stop();
        }
    };

    $AllauthenticateForToken = function () {
        $app = \Slim\Slim::getInstance("default");
        session_start();

        try {
            $params = $app->router()->getCurrentRoute()->getParams();

            if (isset($_SESSION['user_id'])) {
                return;
            }

            if (!isset($app->container["settings"]) || !isset($app->container["settings"]["jwtKey"])) {
                $app->status(500);
                throw new Exception("Fatal Error");
            }

            $jwtKey = $app->container["settings"]["jwtKey"];
            if (!isset($_GET['clientId'])) {
                $app->status(401);
                throw new \Exception('Can\'t access the user data');
            }

            $result = JWT::decode(base64_decode($_GET['clientId']), $jwtKey, array('HS512'));
            $data = Util::objectToArray($result);

            if (!isset($data['http://teamb-iot.calit2.net/is_admin'])) {
                $app->status(401);
                throw new \Exception('Can\'t access the user data');
            }

            if ($data['http://teamb-iot.calit2.net/is_admin'] === true) {
                return;
            }

            // pass;
        } catch (\Exception $e) {
            $responseData = array(
                'meta' => array(
                    'error' => true,
                    'message' => $e->getMessage()
                )
            );

            if ($app->response->Status() === 200)
                $app->response->setStatus(401);

            $app->response->headers->set('Content-Type', 'application/json');

            echo json_encode($responseData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $app->stop();
        }
    };

    // API (Not restful)
    $app->post('/forgotpwd', 'Iot\Controller\APIController:actionForgotPassword')
        ->name('get-forgotpwd');
    //

    $app->get('/auths/:id', $authenticateForToken, 'Iot\Controller\APIController:actionReauth')
        ->conditions(array('id' => '\d+'))
        ->name('get-reauth');

    $app->post('/auths', 'Iot\Controller\APIController:actionAuth')
        ->name('post-auth');

    $app->delete('/auths/:id', $authenticateForToken, 'Iot\Controller\APIController:actionAuthBlacklist')
        ->conditions(array('id' => '\d+'))
        ->name('delete-auth');

    // REST API
    $app->post('/users', 'Iot\Controller\UserController:actionRequestSignUp')
        ->name('insert-user');

    $app->get('/users/:id', $authenticateForToken, 'Iot\Controller\APIController:actionGetUser')
        ->conditions(array('id' => '\d+'))
        ->name('get-user-detail');

    $app->put('/users/:id', $authenticateForToken, 'Iot\Controller\APIController:actionUpdateUserPassword')
        ->conditions(array('id' => '\d+'))
        ->name('update-user-info');

    $app->delete('/users/:id', $authenticateForToken, 'Iot\Controller\APIController:actionDeleteUser')
        ->conditions(array('id' => '\d+'))
        ->name('delete-user-info');

    $app->get('/users/:id/boards', $authenticateForToken, 'Iot\Controller\APIController:actionGetAllBoardsOfUser')
        ->conditions(array('id' => '\d+'))
        ->name('get-user-boards');

    $app->post('/users/:id/boards', $authenticateForToken, 'Iot\Controller\APIController:actionInsertBoardOfUser')
        ->conditions(array('id' => '\d+'))
        ->name('insert-user-boards');

    $app->get('/users/:id/boards/apdata', $authenticateForToken, 'Iot\Controller\APIController:actionGetAPDataOfAllBoardsOfUser')
        ->conditions(array('id' => '\d+'))
        ->name('get-user-boards-apdata');

    $app->get('/users/:id/boards/:bid/apdata', $authenticateForToken, 'Iot\Controller\APIController:actionGetAPDataOfBoardsOfUser')
        ->conditions(array('id' => '\d+'))
        ->conditions(array('bid' => '\d+'))
        ->name('get-user-board-apdata');

    $app->post('/users/:id/boards/:bid/apdata', $authenticateForToken, 'Iot\Controller\APIController:actionInsertAPDataOfBoardOfUser')
        ->conditions(array('id' => '\d+'))
        ->conditions(array('bid' => '\d+'))
        ->name('insert-user-board-apdata');

    $app->get('/users/:id/boards/aqidata', $authenticateForToken, 'Iot\Controller\APIController:actionGetAQIDataOfAllBoardsOfUser')
        ->conditions(array('id' => '\d+'))
        ->name('get-user-boards-aqidata');

    $app->get('/users/:id/boards/:bid/aqidata', $authenticateForToken, 'Iot\Controller\APIController:actionGetAQIDataOfBoardsOfUser')
        ->conditions(array('id' => '\d+'))
        ->conditions(array('bid' => '\d+'))
        ->name('insert-user-board-aqidata');

    $app->post('/users/:id/boards/:bid/aqidata', $authenticateForToken, 'Iot\Controller\APIController:actionInsertAQIDataOfBoardOfUser')
        ->conditions(array('id' => '\d+'))
        ->conditions(array('bid' => '\d+'))
        ->name('insert-user-board-aqidata');

    $app->get('/users/:id/hrdata', $authenticateForToken, 'Iot\Controller\APIController:actionGetAllHRDataOfUser')
        ->conditions(array('id' => '\d+'))
        ->name('get-user-hrdata');

    $app->post('/users/:id/hrdata', $authenticateForToken, 'Iot\Controller\APIController:actionInsertHRDataOfUser')
        ->conditions(array('id' => '\d+'))
        ->name('insert-user-hrdata');

    $app->post('/users/hrdata', $AllauthenticateForToken, 'Iot\Controller\APIController:actionInsertHRDataOfUser')
        ->name('insert-users-hrdata');

    $app->get('/boards/:id/users', $AllauthenticateForToken, 'Iot\Controller\BoardController:actionGetBoardOfUser')
        ->conditions(array('id' => '\d+'))
        ->name('get-boards-users');

    $app->get('/boards/:id/aqidata', $AllauthenticateForToken, 'Iot\Controller\APIController:actionGetBoardAQIData')
        ->conditions(array('id' => '\d+'))
        ->name('get-boards-apdata-list');

    $app->get('/boards/apdata', $AllauthenticateForToken, 'Iot\Controller\BoardController:actionGetAPData')
        ->name('get-boards-apdata-list');

    $app->post('/boards/apdata', $AllauthenticateForToken, 'Iot\Controller\APIController:actionInsertAPDataOfBoardsCSV')
        ->name('insert-boards-apdata-list');

    $app->get('/boards/aqidata', $AllauthenticateForToken, 'Iot\Controller\APIController:actionGetAQIData')
        ->name('get-boards-aqidata-list');

    $app->post('/boards/aqidata', $AllauthenticateForToken, 'Iot\Controller\APIController:actionInsertAQIDataOfBoardsCSV')
        ->name('insert-boards-aqidata-list');

    /* Don't need these API yet
    $app->get('/boards', 'Iot\Controller\BoardController:actionGetBoards')
        ->name('get-boards-list');

    $app->get('/boards/:id', 'Iot\Controller\BoardController:actionGetBoard')
        ->conditions(array('id' => '\d+'))
        ->name('get-board-detail');

    $app->post('/boards/:id/aqidata', $authenticateForAccessToken, 'Iot\Controller\BoardController:actionInsertAQIDataOfBoard')
        ->conditions(array('id' => '\d+'))
        ->name('insert-board-aqidata');
    */
});