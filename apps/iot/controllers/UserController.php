<?php
namespace Iot\Controller;

use \Firebase\JWT\JWT;
use Slimvc\Core\Controller;
use Iot\Model\UserModel;
use Story\Util;
use Story\SomiMailer;
use Iot\Model\APIController;

class UserController extends Controller
{
    // WEB PAGE
    public function actionSignUp()
    {
        session_start();

        if (isset($_SESSION['user_id']))
            $this->getApp()->redirect('/');

        if (!isset($_SESSION['csrf-token'])) {
            $_SESSION['csrf-token'] = bin2hex(openssl_random_pseudo_bytes(32));
        }

        $this->getApp()->contentType('text/html');

        $data = array(
            'title' => 'SOMI - Sign up',
        );

        $this->render('signup/signup.phtml', $data);
    }

    public function actionRequestSignUpForWeb()
    {
        session_start();

        try {

            if (isset($_SESSION['user_id']))
                $this->getApp()->redirect('/');

            if (!isset($_POST['csrf-token']) || !isset($_SESSION['csrf-token'])) {
                $this->getApp()->redirect('/signup');
            }

            if (!Util::hash_equals($_POST['csrf-token'], $_SESSION['csrf-token'])) {
                $this->getApp()->redirect('/signup');
            }

            $this->actionRequestSignUp(true);

            $data = array(
                'title' => 'SOMI - Sign up Complete',
            );

            $this->getApp()->contentType('text/html');
            $this->render('signup/success.phtml', $data);
        } catch (\Exception $e) {
            $this->getApp()->contentType('text/html');
            $this->getApp()->flash('alert', $e->getMessage());
            $this->getApp()->redirect('/signup');
        }
    }

    public function actionRerequestSignUp($isWeb = false)
    {
        try {
            //Util::urlsafeB64Encode
            $email = Util::urlsafeB64Decode($this->getApp()->request()->get('email'));

            if (strlen($email) < 0 || strlen($email) > 64 || !filter_var($email,FILTER_VALIDATE_EMAIL)) {
                $this->getApp()->status(400);
                throw new \Exception("Email is incorrect");
            }

            $userModel = new UserModel();
            if (!$result = $userModel->getUserWithEmail($email, array('user_id', 'active'))) {
                $this->getApp()->status(400);
                throw new \Exception("Email is incorrect");
            }

            if ($result['active']) {
                $this->getApp()->status(400);
                throw new \Exception("Your email is already active");
            }

            $tokenId = bin2hex(openssl_random_pseudo_bytes(32));
            $satKey = $this->getConfig()['satKey'];
            $verification = array(
                'tki' => $tokenId,
                'exp' => time() + 600,
                'email' => $email,
            );

            $veriData = Util::urlsafeB64Encode(json_encode($verification));
            $signature = Util::urlsafeB64Encode(hash_hmac('sha256', Util::urlsafeB64Encode(json_encode($verification)), $satKey));
            $resultCode = Util::urlsafeB64Encode($veriData.'.'.$signature);

            $confirmURL = 'http://teamb-iot.calit2.net/signup/confirm?v='.$resultCode;

            $mailBody = '<h3>Weclome Some!</h3><p>Please check the confirmation link below</p><a href="'.$confirmURL.'">Click the link to active</a>';

            $somiMailer = new SomiMailer();
            $somiMailer->sendMail($email, 'Confirmation Email from Somi', $mailBody);

            $resultJson['data'] = array(

            );

            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            if ($isWeb)
                throw $e;

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

    public function actionSignUpConfirm()
    {
        $timestamp = time();
        $this->getApp()->contentType('text/html');

        $data = array(
            'title' => 'SOMI - Sign up Complete',
        );

        try {
            $v = $this->getApp()->request()->get('v');
            if (!isset($v)) {
                // something is wrong.
                throw new \Exception('Bad Request');
            }

            $v = Util::urlsafeB64Decode($v);
            $token = explode('.', $v);
         //   var_dump($token);
            if (count($token) != 2) {
                throw new \Exception('Bad Request');
            }

            list($jsonMsg64, $sig64) = $token;
            if (($jsonMsg = json_decode(Util::urlsafeB64Decode($jsonMsg64))) === null)
                throw new \Exception('Bad Request');

            if (($sig = Util::urlsafeB64Decode($sig64)) === null)
                throw new \Exception('Bad Request');

            $satKey = $this->getConfig()['satKey'];
            $calcSig = hash_hmac('sha256', $jsonMsg64, $satKey);
            if (!Util::hash_equals($sig, $calcSig))
                throw new \Exception("Bad Request");

            $arrayMsg = Util::objectToArray($jsonMsg);
            if ($timestamp > $arrayMsg["exp"])
                throw new \Exception('Bad Request');

            $userModel = new UserModel();
            if (!$result = $userModel->getUserWithEmail($arrayMsg['email'], array('active')))
                throw new \Exception('Bad Request');

            if ($result["active"] === "1")
                throw new \Exception('Bad Request');

            if (!$userModel->updateUserActiveWithEmail($arrayMsg['email'], 1)) {
                throw new \Exception('Bad Request');
            }

/*            var_dump($jsonMsg);
            var_dump($sig);
            var_dump($calcSig); */

            $data['msg'] = 'Your email is active successfully';
        } catch (\Exception $e) {
            $data['msg'] = $e->getMessage();
        }

        $this->render('signup/confirm.phtml', $data);
    }

    public function actionSignIn()
    {
        session_start();

        if (isset($_SESSION['user_id']))
            $this->getApp()->redirect('/');

        if (!isset($_SESSION['csrf-token'])) {
            $_SESSION['csrf-token'] = bin2hex(openssl_random_pseudo_bytes(32));
        }

        $this->getApp()->contentType('text/html');

        $data = array(
            'title' => 'SOMI - Sign in'
        );

        $this->render("signin/signin.phtml", $data);
    }

    public function actionRequestSignIn()
    {
        session_start();

        if (isset($_SESSION['user_id']))
            $this->getApp()->redirect('/');

        if (!isset($_POST['csrf-token']) || !isset($_SESSION['csrf-token']) || !isset($_POST['email']) || !isset($_POST['password'])) {
            $this->getApp()->redirect('/signin');
        }

        if (!Util::hash_equals($_POST['csrf-token'], $_SESSION['csrf-token'])) {
            $this->getApp()->redirect('/signin');
        }

        if (strlen($_POST['email']) > 64 || !filter_var($_POST['email'],FILTER_VALIDATE_EMAIL)) {
            $this->getApp()->flash('alert', 'Your email or password is incorrect');
            $this->getApp()->redirect('/signin');
        }

/*      For Test
        if (!filter_var($_POST['password'], FILTER_VALIDATE_REGEXP,
            array( 'options' => array("regexp" => "/^.*(?=^.{8,15}$)(?=.*\d)(?=.*[a-zA-Z])(?=.*[!@#$%^&+=]).*$/")))) {
            $this->getApp()->flash('alert', 'Your email or password is bad');
            $this->getApp()->redirect('/signin');
        }
        */

        $userModel = new UserModel();
        if (!$result = $userModel->getUserWithEmail($_POST['email'])) {
            // there is no user
            $this->getApp()->flash('alert', 'Your email or password is incorrect');
            $this->getApp()->redirect('/signin');
        }

        if (!password_verify($_POST['password'], $result['password'])) {
            // can't pass the verification with password
            $this->getApp()->flash('alert', 'Your email or password is incorrect');
            $this->getApp()->redirect('/signin');
        }

        $encodedEmail = Util::urlsafeB64Encode($_POST['email']);
        if (!$result['active']) {
            // the account is not active.
            $this->getApp()->flash('alert', 'Your email is not active.<br/>Check your mail box again. <a href="#" target="_self" onclick="activeLink()">Link</a>
                                <script>
                                    function activeLink() {
/*                                        $.get("/reactive?email='.$encodedEmail.'", function (data) {
                                            if ()
                                            
                                           console.log(data["data"]);
                                        }); */

                                        $.ajax({
                                            dataType: "json",
                                            url: "/reactive?email='.$encodedEmail.'",
                                            success: function(data, err) {
                                                console.log(data);
                                            }
                                        });
                                    }
                                </script>');
            $this->getApp()->redirect('/signin');
        }

        $_SESSION['user_id'] = $result['user_id'];
        $this->getApp()->redirect('/');
    }

    public function actionRequestSignOut()
    {
        session_start();
        $this->getApp()->contentType('text/html');

        if (!isset($_POST['csrf-token']) || !isset($_SESSION['csrf-token']))
            $this->getApp()->redirect('/');

        if (!Util::hash_equals($_POST['csrf-token'], $_SESSION['csrf-token']))
            $this->getApp()->redirect('/');

        if (isset($_SESSION['user_id']))
            unset($_SESSION['user_id']);

        $this->getApp()->redirect('/signin');
    }

    public function actionForgotPwd()
    {
        session_start();

        if (isset($_SESSION['user_id']))
            $this->getApp()->redirect('/');

        if (!isset($_SESSION['csrf-token'])) {
            $_SESSION['csrf-token'] = bin2hex(openssl_random_pseudo_bytes(32));
        }

        $this->getApp()->contentType('text/html');

        $data = array(
            'title' => 'SOMI - Forgot Password'
        );

        $this->render("forgotPwd/forgotpwd.phtml", $data);
    }

    public function actionRequestForgotPwd()
    {
        session_start();
        $this->getApp()->contentType('text/html');

        if (isset($_SESSION['user_id']))
            $this->getApp()->redirect('/');

        if (!isset($_POST['csrf-token']) || !isset($_SESSION['csrf-token']))
            $this->getApp()->redirect('/');

        if (!Util::hash_equals($_POST['csrf-token'], $_SESSION['csrf-token']))
            $this->getApp()->redirect('/');

        if (!isset($_POST['email']))
            $this->getApp()->redirect('/');

        do {
            $email = $_POST['email'];
            if (strlen($email) > 64 || !filter_var($email,FILTER_VALIDATE_EMAIL)) {
                break;
            }

            $userModel = new UserModel();
            if (!$userModel->getUserWithEmail($email, array ('user_id')))
                break;

            if (!$userModel->updateUserPwdActiveWithEmail($email, 1)) {
                break;
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
        } while(0);

        $this->getApp()->flash('info', 'Confirm mail have been sent.<br/>Check your mail box.');
        $this->getApp()->redirect('/forgotpwd');
    }

    public function actionForgotPwdConfirm()
    {
        session_start();
        $timestamp = time();

        $data = array(
            'title' => 'SOMI - Sign up',
        );

        try {
            $v = $this->getApp()->request()->get('v');
            if (!isset($v)) {
                // something is wrong.
                throw new \Exception('Bad Request');
            }

            $v = Util::urlsafeB64Decode($v);
            $token = explode('.', $v);
            if (count($token) != 2) {
                throw new \Exception('Bad Request');
            }

            list($jsonMsg64, $sig64) = $token;
            if (($jsonMsg = json_decode(Util::urlsafeB64Decode($jsonMsg64))) === null)
                throw new \Exception('Bad Request');

            if (($sig = Util::urlsafeB64Decode($sig64)) === null)
                throw new \Exception('Bad Request');

            $ptpKey = $this->getConfig()['ptpKey'];
            $calcSig = hash_hmac('sha256', $jsonMsg64, $ptpKey);
            if (!Util::hash_equals($sig, $calcSig))
                throw new \Exception("Bad Request");

            $arrayMsg = Util::objectToArray($jsonMsg);
            if ($timestamp > $arrayMsg["exp"])
                throw new \Exception('Bad Request');

            $userModel = new UserModel();
            if (!$result = $userModel->getUserWithEmail($arrayMsg['email'], array('pwdactive')))
                throw new \Exception('Bad Request');

            if ($result["pwdactive"] === "0")
                throw new \Exception('Bad Request');

            if (!$userModel->updateUserPwdActiveWithEmail($arrayMsg['email'], 0)) {
                throw new \Exception('Bad Request');
            }

            $randomPwd = Util::randomPassword().'a1!';
            if (!$userModel->updateUserPasswordWithEmail($arrayMsg['email'], password_hash($randomPwd, PASSWORD_DEFAULT))) {
                throw new \Exception('Bad Request');
            }

            if (isset($_SESSION['user_id']))
                unset($_SESSION['user_id']);

            $mailMsg = '<p>Thank you for contacting us</p><p>Please check your temporary password</p>'.'<h3>'.$randomPwd.'</h3>';
            $mailer = new SomiMailer();
            $mailer->sendMail($arrayMsg['email'], 'Here is the your temporary password', $mailMsg);

/*            var_dump($jsonMsg);
            var_dump($sig);
            var_dump($calcSig); */

            $data['msg'] = 'Your request is confirmed successfully';
        } catch (\Exception $e) {
            $data['msg'] = $e->getMessage();
        }

        $this->render('forgotPwd/confirm.phtml', $data);
    }

    public function actionRequestSignUp($isWeb = false)
    {
        $resultJson = array();
        $this->getApp()->response->headers->set('Content-Type', 'application/json');

        try {
            $data = array(
                'email' => '',
                'password' => '',
                'fname' => '',
                'lname' => '',
                'birthday-month' => '',
                'birthday-day' => '',
                'birthday-year' => '',
                'gender' => ''
            );

            foreach ($data as $key => $field) {
                if (!isset($_POST[$key]) || $_POST[$key] === $field) {
                    $this->getApp()->status(400);
                    throw new \Exception('You have to input all data.');
                }
                $data[$key] = $_POST[$key];
                unset($key, $field);
            }

            if (strlen($_POST['email']) > 64 || !filter_var($_POST['email'],FILTER_VALIDATE_EMAIL)) {
                $this->getApp()->status(400);
                throw new \Exception("Email or password is incorrect");
            }

            if (!filter_var($_POST['password'], FILTER_VALIDATE_REGEXP,
                array( 'options' => array("regexp" => "/^.*(?=^.{8,15}$)(?=.*\d)(?=.*[a-zA-Z])(?=.*[!@#$%^&+=]).*$/")))) {
                $this->getApp()->status(400);
                throw new \Exception("Email or password is incorrect");
            }

            if (strlen($_POST['fname']) > 35 || strlen($_POST['lname']) > 35 || !ctype_alpha($_POST['fname']) || !ctype_alpha($_POST['lname'])) {
                $this->getApp()->status(400);
                throw new \Exception("Your name is incorrect");
            }

            $gender = intval($_POST['gender']);
            if ($gender < 0 || $gender > 2 || !ctype_digit($_POST['gender'])) {
                $this->getApp()->status(400);
                throw new \Exception("Your gender is incorrect");
            }

            // have to check the validation of the
            if (!checkdate($_POST['birthday-month'], $_POST['birthday-day'], $_POST['birthday-year'])) {
                $this->getApp()->status(400);
                throw new \Exception("Your birthday is incorrect");
            }

            $userModel = new UserModel();
            if ($result = $userModel->getUserWithEmail($_POST['email'], array('user_id'))) {
                $this->getApp()->status(400);
                throw new \Exception("The email already exists");
            }

            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            if (!$result = $userModel->insertUser($data)) {
                $this->getApp()->status(400);
                throw new \Exception("The email already exists");
            }

            $tokenId = bin2hex(openssl_random_pseudo_bytes(32));
            $satKey = $this->getConfig()['satKey'];
            $verification = array(
                'tki' => $tokenId,
                'exp' => time() + 600,
                'email' => $_POST['email'],
            );

            $veriData = Util::urlsafeB64Encode(json_encode($verification));
            $signature = Util::urlsafeB64Encode(hash_hmac('sha256', Util::urlsafeB64Encode(json_encode($verification)), $satKey));
            $resultCode = Util::urlsafeB64Encode($veriData.'.'.$signature);

            $confirmURL = 'http://teamb-iot.calit2.net/signup/confirm?v='.$resultCode;

            $mailBody = '<h3>Welcome Somi!</h3><p>Please check the confirmation link below</p><a href="'.$confirmURL.'">Click the link to active</a>';

            $somiMailer = new SomiMailer();
            $somiMailer->sendMail($_POST['email'], 'Confirmation Email from Somi', $mailBody);

            $resultJson['data'] = array(
                'user_id' => $result
            );

            if (!$isWeb)
                echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            if ($isWeb)
                throw $e;

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
}
