<?php
namespace Iot\Controller;

use \Firebase\JWT\JWT;
use Slimvc\Core\Controller;
use Iot\Model\BoardModel;

class BoardController extends Controller
{
    /**
     */
    public function actionGetBoards()
    {
        $fieldsStr = $this->getApp()->request()->get('fields', '');

        $fields = explode(',', $fieldsStr);
        foreach ($fields as $key => $field) {
            $field = trim($field);
            if (!$field) {
                unset($fields[$key]);
            }
            // TODO do safety check for fields
        }

        $boardModel = new BoardModel();
        if (!$dbResult = $boardModel->getBoards($fields)) {
            $this->getApp()->status(404);
            $dbResult = array(
                'code' => 404,
                'message' => 'specified user is not found'
            );
        }

        $result = array(
            'code' => 200,
            'data' => $dbResult
        );

        $this->getApp()->response->headers->set('Content-Type', 'application/json');
        $this->getApp()->status(200);

        echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     */
    public function actionGetBoard($id)
    {
        $fieldsStr = $this->getApp()->request()->get('fields', '');

        $fields = explode(',', $fieldsStr);
        foreach ($fields as $key => $field) {
            $field = trim($field);
            if (!$field) {
                unset($fields[$key]);
            }
            // TODO do safety check for fields
        }

        $boardModel = new BoardModel();
        if (!$dbResult = $boardModel->GetBoard($id, $fields)) {
            $this->getApp()->status(404);
            $dbResult = array(
                'code' => 404,
                'message' => 'specified board is not found'
            );
        }

        $result = array(
            'code' => 200,
            'data' => $dbResult
        );

        $this->getApp()->response->headers->set('Content-Type', 'application/json');
        $this->getApp()->status(200);

        echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function actionGetBoardOfUser($id)
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
                // TODO do safety check for fields
            }

            $boardModel = new BoardModel();
            if (!$dbResult = $boardModel->GetBoardOfUser($id, array('fname', 'name'))) {
                $dbResult = array();
            }

            $resultJson['data'] = array(
                'users' => $dbResult
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

    public function actionGetAPData()
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

                if (strlen($field) > 0 && !filter_var($field, FILTER_SANITIZE_STRING))
                    throw new \Exception('Bad Format');
            }

            $boardModel = new BoardModel();

            if ($isCsv) {
                if (!$result = $boardModel->getAPDataCSV($offset, $limit, $orderby)) {
                    $result = array();
                }


//                header('Content-Type: text/csv');
            } else {

                if (!$result = $boardModel->getAPData($fields, $offset, $limit, $orderby)) {
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

    public function actionInsertAPDataOfBoard($id)
    {
        try {
            $resultJson = array();
            $this->getApp()->response->headers->set('Content-Type', 'application/json');

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
                    // have to throw something or halt it
                    $resultJson['meta'] = array(
                        'error' => true,
                        'message' => 'have to get some data'
                    );

                    $this->getApp()->status(400);
                    echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                    $this->getApp()->stop();
                }

                $data[$key] = $_POST[$key];
            }

            $boardModel = new BoardModel();
            if (!($result = $boardModel->insertAPDataOfBoard($id, $data))) {
                $resultJson['meta'] = array(
                    'error' => true,
                    'message' => 'Database error'
                );

                $this->getApp()->status(500);
                echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                $this->getApp()->stop();
            }

            $resultJson = array(
                'data' => array(
                    'aqidata_id' => $result
                )
            );

            $this->getApp()->status(201);
            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }
    }

    public function actionInsertAQIDataOfBoard($id)
    {
        try {
            $resultJson = array();
            $this->getApp()->response->headers->set('Content-Type', 'application/json');

            $data = array(
                'no2' => '',
                'o3' => '',
                'co' => '',
                'so2' => '',
                'pm25' => '',
                'temperature' => '',
                'latitude' => '',
                'longitude' => '',
                'aqidata_timestamp' => '',
            );

            foreach ($data as $key => $field) {
                if (!isset($_POST[$key])) {
                    // have to throw something or halt it
                    $resultJson['meta'] = array(
                        'error' => true,
                        'message' => 'have to get some data'
                    );

                    $this->getApp()->status(400);
                    echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                    $this->getApp()->stop();
                }

                $data[$key] = $_POST[$key];
            }

            $boardModel = new BoardModel();
            if (!($result = $boardModel->insertAQIDataOfBoard($id, $data))) {
                $resultJson['meta'] = array(
                    'error' => true,
                    'message' => 'Database error'
                );

                $this->getApp()->status(500);
                echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                $this->getApp()->stop();
            }

            $resultJson = array(
                'data' => array(
                    'aqidata_id' => $result
                )
            );

            $this->getApp()->status(201);
            echo json_encode($resultJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }
    }
}
