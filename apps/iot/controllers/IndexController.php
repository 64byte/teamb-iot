<?php
namespace Iot\Controller;

use Slimvc\Core\Controller;
use Iot\Model\UserModel;
use Story\Util;
use Story\AQICalc;

class IndexController extends Controller
{
    /**
     * Default index action
     */
    public function actionIndex()
    {
        session_start();

        $this->getApp()->contentType('text/html');

        if (!isset($_SESSION['user_id'])) {
            $this->getApp()->redirect('/signin');
        }

        if (!isset($_SESSION['csrf-token'])) {
            $_SESSION['csrf-token'] = bin2hex(openssl_random_pseudo_bytes(32));
        }

        $userModel = new UserModel();
        if (!$result = $userModel->getUser($_SESSION['user_id'])) {
            unset($_SESSION['user_id']);
            $this->getApp()->redirect('/');
        }

        if (!$boardResult = $userModel->getAllBoardsOfUser($_SESSION['user_id']))
            $boardResult = array();

        $data = array(
            'title' => 'SOMI - Main Page',
            'user' => $result,
            'boards' => $boardResult
        );

        $this->render("index/index.phtml", $data);
    }

    public function actionIndex2()
    {
        session_start();

        $data = array(
            'title' => 'SOMI - Main Page',
        );

        $this->render("signup/success.phtml", $data);
    }

    public function actionProfile()
    {
        session_start();

        $this->getApp()->contentType('text/html');

        if (!isset($_SESSION['user_id'])) {
            $this->getApp()->redirect('/signin');
        }

        $userModel = new UserModel();
        if (!$result = $userModel->getUser($_SESSION['user_id'])) {
            unset($_SESSION['user_id']);
            $this->getApp()->redirect('/');
        }

        if (!$boardResult = $userModel->getAllBoardsOfUser($_SESSION['user_id'])) {
            $boardResult = array();
        }

        $data = array(
            'title' => 'SOMI - Profile Page',
            'user' => $result,
            'boards' => $boardResult,
            'gender_string' => Util::genderToString(intval($result['gender']))
        );

        $this->render("profile/profile.phtml", $data);
    }

    public function actionBoards()
    {
        session_start();

        $this->getApp()->contentType('text/html');

        if (!isset($_SESSION['user_id'])) {
            $this->getApp()->redirect('/signin');
        }

        $userModel = new UserModel();
        if (!$result = $userModel->getUser($_SESSION['user_id'])) {
            unset($_SESSION['user_id']);
            $this->getApp()->redirect('/');
        }

        if (!$boardResult = $userModel->getAllBoardsOfUser($_SESSION['user_id'])) {
            $boardResult = array();
        }

        $data = array(
            'title' => 'SOMI - Profile Page',
            'user' => $result,
            'boards' => $boardResult
        );

        $this->render("profile/boards.phtml", $data);
    }

    public function actionPassword()
    {
        session_start();

        $this->getApp()->contentType('text/html');

        if (!isset($_SESSION['user_id'])) {
            $this->getApp()->redirect('/signin');
        }

        $userModel = new UserModel();
        if (!$result = $userModel->getUser($_SESSION['user_id'])) {
            unset($_SESSION['user_id']);
            $this->getApp()->redirect('/');
        }

        if (!$boardResult = $userModel->getAllBoardsOfUser($_SESSION['user_id'])) {
            $boardResult = array();
        }

        $data = array(
            'title' => 'SOMI - Password Change Page',
            'user' => $result,
            'boards' => $boardResult
        );

        $this->render("profile/password.phtml", $data);
    }

    public function actionPasswordConfirm()
    {
        session_start();

        $this->getApp()->contentType('text/html');

        if (!isset($_SESSION['user_id'])) {
            $this->getApp()->redirect('/signin');
        }

        if(!isset($_POST['password']) || !isset($_POST['new_password'])) {
            $this->getApp()->flash('alert', 'Your password is bad');
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

        $userModel = new UserModel();
        if (!$result = $userModel->getUser($_SESSION['user_id'])) {
            unset($_SESSION['user_id']);
            $this->getApp()->redirect('/');
        }

        if (!password_verify($password, $result['password'])) {
            $this->getApp()->flash('alert', 'Your password is incorrect');
            $this->getApp()->redirect('/profile/password');
        }

        if (!$userModel->updateUserPassword($result['user_id'], password_hash($newPassword, PASSWORD_DEFAULT))) {
            $this->getApp()->flash('alert', 'Something is wrong');
            $this->getApp()->redirect('/profile/password');
        }

        unset($_SESSION['user_id']);
        $this->getApp()->redirect('/');
    }

    public function actionMaps()
    {
        session_start();

        $this->getApp()->contentType('text/html');

        if (!isset($_SESSION['user_id'])) {
            $this->getApp()->redirect('/signin');
        }

        $userModel = new UserModel();
        if (!$result = $userModel->getUser($_SESSION['user_id'])) {
            unset($_SESSION['user_id']);
            $this->getApp()->redirect('/');
        }

        $data = array(
            'title' => 'SOMI - Maps Page',
            'user' => $result
        );

        $this->render("maps/maps.phtml", $data);
    }

    public function actionChart()
    {
        session_start();

        $this->getApp()->contentType('text/html');

        if (!isset($_SESSION['user_id'])) {
            $this->getApp()->redirect('/signin');
        }

        $userModel = new UserModel();
        if (!$result = $userModel->getUser($_SESSION['user_id'])) {
            unset($_SESSION['user_id']);
            $this->getApp()->redirect('/');
        }

        $data = array(
            'title' => 'SOMI - Maps Page',
            'user' => $result
        );

        $this->render("chart/chart.phtml", $data);
    }

    public function actionAQIMaps()
    {
        session_start();

        $this->getApp()->contentType('text/html');

        if (!isset($_SESSION['user_id'])) {
            $this->getApp()->redirect('/signin');
        }

        $userModel = new UserModel();
        if (!$result = $userModel->getUser($_SESSION['user_id'])) {
            unset($_SESSION['user_id']);
            $this->getApp()->redirect('/');
        }

        if (!$boardResult = $userModel->getAllBoardsOfUser($_SESSION['user_id']))
            $boardResult = array();

        $data = array(
            'title' => 'SOMI - AQI Maps Page',
            'user' => $result,
            'boards' => $boardResult
        );

        $this->render("aqi/aqimaps.phtml", $data);
    }

    public function actionAQIChart()
    {
        session_start();

        $this->getApp()->contentType('text/html');

        if (!isset($_SESSION['user_id'])) {
            $this->getApp()->redirect('/signin');
        }

        $userModel = new UserModel();
        if (!$result = $userModel->getUser($_SESSION['user_id'])) {
            unset($_SESSION['user_id']);
            $this->getApp()->redirect('/');
        }

        if (!$boardResult = $userModel->getAllBoardsOfUser($_SESSION['user_id']))
            $boardResult = array();

        $data = array(
            'title' => 'SOMI - Historical Air Pollution',
            'user' => $result,
            'boards' => $boardResult
        );

        $this->render("aqi/aqichart.phtml", $data);
    }

    public function actionHistoricalAQIMap()
    {
        session_start();

        $this->getApp()->contentType('text/html');

        if (!isset($_SESSION['user_id'])) {
            $this->getApp()->redirect('/signin');
        }

        $userModel = new UserModel();
        if (!$result = $userModel->getUser($_SESSION['user_id'])) {
            unset($_SESSION['user_id']);
            $this->getApp()->redirect('/');
        }

        if (!$boardResult = $userModel->getAllBoardsOfUser($_SESSION['user_id']))
            $boardResult = array();

        $data = array(
            'title' => 'SOMI - Historical AQI Map',
            'user' => $result,
            'boards' => $boardResult
        );

        $this->render("aqi/historyaqi.phtml", $data);
    }

    public function actionRealTimeAirPollution($bid)
    {
        session_start();

        $this->getApp()->contentType('text/html');

        if (!isset($_SESSION['user_id'])) {
            $this->getApp()->redirect('/signin');
        }

        $userModel = new UserModel();
        if (!$result = $userModel->getUser($_SESSION['user_id'])) {
            unset($_SESSION['user_id']);
            $this->getApp()->redirect('/');
        }

        if (!$boardResult = $userModel->getAllBoardsOfUser($_SESSION['user_id']))
            $boardResult = array();

        $data = array(
            'title' => 'SOMI - Real Time Air Pollution',
            'user' => $result,
            'boards' => $boardResult,
            'selectedBid' => $bid
        );

        $this->render("airpollution/realtimeap.phtml", $data);
    }

    public function actionHistoricalAirPollution($bid)
    {
        session_start();

        $this->getApp()->contentType('text/html');

        if (!isset($_SESSION['user_id'])) {
            $this->getApp()->redirect('/signin');
        }

        $userModel = new UserModel();
        if (!$result = $userModel->getUser($_SESSION['user_id'])) {
            unset($_SESSION['user_id']);
            $this->getApp()->redirect('/');
        }

        if (!$boardResult = $userModel->getAllBoardsOfUser($_SESSION['user_id']))
            $boardResult = array();

        $data = array(
            'title' => 'SOMI - Historical Air Pollution',
            'user' => $result,
            'boards' => $boardResult,
            'selectedBid' => $bid
        );

        $this->render("airpollution/historyap.phtml", $data);
    }

    public function actionPrivateHistoricalAQIChart($bid)
    {
        session_start();

        $this->getApp()->contentType('text/html');

        if (!isset($_SESSION['user_id'])) {
            $this->getApp()->redirect('/signin');
        }

        $userModel = new UserModel();
        if (!$result = $userModel->getUser($_SESSION['user_id'])) {
            unset($_SESSION['user_id']);
            $this->getApp()->redirect('/');
        }

        if (!$boardResult = $userModel->getAllBoardsOfUser($_SESSION['user_id']))
            $boardResult = array();

        $data = array(
            'title' => 'SOMI - AQI Chart',
            'user' => $result,
            'boards' => $boardResult,
            'selectedBid' => $bid
        );

        $this->render("aqi/paqichart.phtml", $data);
    }

    public function actionHistoricalAirPollutionMap($bid)
    {
        session_start();

        $this->getApp()->contentType('text/html');

        if (!isset($_SESSION['user_id'])) {
            $this->getApp()->redirect('/signin');
        }

        $userModel = new UserModel();
        if (!$result = $userModel->getUser($_SESSION['user_id'])) {
            unset($_SESSION['user_id']);
            $this->getApp()->redirect('/');
        }

        if (!$boardResult = $userModel->getAllBoardsOfUser($_SESSION['user_id']))
            $boardResult = array();

        $data = array(
            'title' => 'SOMI - Historical Air Pollution',
            'user' => $result,
            'boards' => $boardResult,
            'selectedBid' => $bid
        );

        $this->render("airpollution/historyapm.phtml", $data);
    }

    public function actionRealTimeHeartRate()
    {
        session_start();

        $this->getApp()->contentType('text/html');

        if (!isset($_SESSION['user_id'])) {
            $this->getApp()->redirect('/signin');
        }

        $userModel = new UserModel();
        if (!$result = $userModel->getUser($_SESSION['user_id'])) {
            unset($_SESSION['user_id']);
            $this->getApp()->redirect('/');
        }

        if (!$boardResult = $userModel->getAllBoardsOfUser($_SESSION['user_id']))
            $boardResult = array();

        $data = array(
            'title' => 'SOMI - Real Time Heart Rate',
            'user' => $result,
            'boards' => $boardResult
        );

        $this->render("heartrate/realtimehr.phtml", $data);
    }

    public function actionHistoricalHeartRate()
    {
        session_start();

        $this->getApp()->contentType('text/html');

        if (!isset($_SESSION['user_id'])) {
            $this->getApp()->redirect('/signin');
        }

        $userModel = new UserModel();
        if (!$result = $userModel->getUser($_SESSION['user_id'])) {
            unset($_SESSION['user_id']);
            $this->getApp()->redirect('/');
        }

        if (!$boardResult = $userModel->getAllBoardsOfUser($_SESSION['user_id']))
            $boardResult = array();

        $data = array(
            'title' => 'SOMI - Historical Heart Rate',
            'user' => $result,
            'boards' => $boardResult
        );

        $this->render("heartrate/historyhr.phtml", $data);
    }

    public function actionSecond()
    {
        $this->getApp()->contentType('text/html');

        $data = array(
            'title' => 'It works!',
            'content' => 'Have fun with Slim framework in MVC way!'
        );

        var_dump(AQICalc::AQIPM25(500.49999999999));
        exit;

        $this->render("index/second.phtml", $data);
    }

    public function actionHistoryMap()
    {
        session_start();

        $this->getApp()->contentType('text/html');

        if (!isset($_SESSION['user_id'])) {
            $this->getApp()->redirect('/signin');
        }

        $userModel = new UserModel();
        if (!$result = $userModel->getUser($_SESSION['user_id'])) {
            unset($_SESSION['user_id']);
            $this->getApp()->redirect('/');
        }

        $dataType = $this->getApp()->request()->get('dataType');
        $board_id = $this->getApp()->request()->get('bid');
        $startDate = $this->getApp()->request()->get('startDate');
        $endDate = $this->getApp()->request()->get('endDate');

        $data = array(
            'title' => 'SOMI - History Map',
            'user' => $result,

            'dataType' => $dataType,
            'bid' => $board_id,
            'startDate' => $startDate,
            'endDate' => $endDate
        );

        $this->render("historymap.phtml", $data);
    }

    public function actionHistoryHeartRateMap()
    {
        session_start();

        $this->getApp()->contentType('text/html');

        if (!isset($_SESSION['user_id'])) {
            $this->getApp()->redirect('/signin');
        }

        $userModel = new UserModel();
        if (!$result = $userModel->getUser($_SESSION['user_id'])) {
            unset($_SESSION['user_id']);
            $this->getApp()->redirect('/');
        }

        $tempValue = $this->getApp()->request()->get('value', 1);
        $startDate = $this->getApp()->request()->get('startDate');
        $endDate = $this->getApp()->request()->get('endDate');

        $data = array(
            'title' => 'SOMI - History Map',
            'user' => $result,
            'value' => $tempValue,
            'startDate' => $startDate,
            'endDate' => $endDate
        );

        $this->render("historyhrm.phtml", $data);
    }

    public function actionHistoryChart()
    {
        session_start();

        $this->getApp()->contentType('text/html');

        if (!isset($_SESSION['user_id'])) {
            $this->getApp()->redirect('/signin');
        }

        $userModel = new UserModel();
        if (!$result = $userModel->getUser($_SESSION['user_id'])) {
            unset($_SESSION['user_id']);
            $this->getApp()->redirect('/');
        }

        $board_id = $this->getApp()->request()->get('bid');
        $startDate = $this->getApp()->request()->get('startDate');
        $endDate = $this->getApp()->request()->get('endDate');
        $lat = $this->getApp()->request()->get('lat');
        $lng = $this->getApp()->request()->get('lng');

        $data = array(
            'title' => 'SOMI - History Map',
            'user' => $result,

            'bid' => $board_id,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'lat' => $lat,
            'lng' => $lng
        );

        $this->render("historychart.phtml", $data);
    }
}
