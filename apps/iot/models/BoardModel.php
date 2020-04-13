<?php
namespace Iot\Model;

use Slimvc\Core\Model;

class BoardModel extends Model
{
    /**
     */
    public function getBoards($fields = array(), $offset = 0, $limit = 10)
    {
        if ($fields && is_array($fields)) {
            // make sure the dynamical fields are safe
            foreach ($fields as $key => $field) {
                $fields[$key] = "`" . str_replace("`", "``", $field) . "`";
            }
            unset($key, $field);
            $fieldsStr = join(',', $fields);
        } else {
            $fieldsStr = '*';
        }

        $sql = 'SELECT ' . $fieldsStr . ' FROM `Board`';

        if ($limit) {
            $sql .= ' LIMIT ' . (int)$limit;
        }

        if ($offset) {
            $sql .= ' OFFSET ' . (int)$offset;
        }

        $sth = $this->getReadConnection()->query($sql);
        $sth->setFetchMode(\PDO::FETCH_ASSOC);

        return $sth->fetchAll();
    }

    /**
     */
    public function getBoard($id, $fields = array())
    {
        if ($fields && is_array($fields)) {
            // make sure the dynamical fields are safe
            foreach ($fields as $key => $field) {
                $fields[$key] = "`" . str_replace("`", "``", $field) . "`";
            }
            unset($key, $field);
            $fieldsStr = join(',', $fields);
        } else {
            $fieldsStr = '*';
        }

        $sql = 'SELECT ' . $fieldsStr . ' FROM `Board` WHERE board_id = :bid';
        $sth = $this->getReadConnection()->prepare($sql);
        $sth->bindParam(':bid', $id, FILTER_SANITIZE_NUMBER_INT);
        $sth->setFetchMode(\PDO::FETCH_ASSOC);
        $sth->execute(array(intval($id)));

        return $sth->fetch();
    }

    public function getBoardOfUser($bid, $fields = array())
    {
        if ($fields && is_array($fields)) {
            // make sure the dynamical fields are safe
            foreach ($fields as $key => $field) {
                $fields[$key] = "`" . str_replace("`", "``", $field) . "`";
            }
            unset($key, $field);
            $fieldsStr = join(',', $fields);
        } else {
            $fieldsStr = '*';
        }

        $sql = 'SELECT '.$fieldsStr. ' FROM `Board`, `User` WHERE Board.user_id = User.user_id and board_id = :bid';
        $sth = $this->getReadConnection()->prepare($sql);
        $sth->bindParam(':bid', $bid, FILTER_SANITIZE_NUMBER_INT);
        $sth->setFetchMode(\PDO::FETCH_ASSOC);
        $sth->execute();

        return $sth->fetchAll();
    }

    public function getAPData($fields, $offset = false, $limit = false, $orderby = false)
    {
        if ($fields && is_array($fields)) {
            // make sure the dynamical fields are safe
            foreach ($fields as $key => $field) {
                $fields[$key] = "`" . str_replace("`", "``", $field) . "`";
            }
            unset($key, $field);
            $fieldsStr = join(',', $fields);
        } else {
            $fieldsStr = 'APData.board_id, APData.no2, APData.o3, APData.co, APData.so2, APData.pm25, APData.temperature, APData.latitude, APData.longitude, APData.apdata_timestamp';
        }

        $sql = 'SELECT ' .$fieldsStr. '
                FROM APData';

        if ($orderby)
            $sql .= ' ORDER BY APData.apdata_timestamp DESC';

        if ($limit) {
            $sql .= ' LIMIT ' . (int)$limit;
        }

        if ($offset) {
            $sql .= ' OFFSET ' . (int)$offset;
        }

        $sth = $this->getReadConnection()->query($sql);
        $sth->setFetchMode(\PDO::FETCH_ASSOC);

        return $sth->fetchAll();
    }

    public function getAPDataCSV($offset = false, $limit = false, $orderby = false)
    {
        $sql = 'SELECT APData.apdata_id, APData.board_id, APData.pm25, APData.co, APData.o3, APData.no2, APData.so2, APData.temperature, APData.latitude, APData.longitude, APData.apdata_timestamp
                FROM APData';

        if ($orderby)
            $sql .= ' ORDER BY APData.apdata_timestamp DESC';

        if ($limit)
            $sql .= ' LIMIT ' . (int)$limit;

        if ($offset)
            $sql .= ' OFFSET ' . (int)$offset;

        $sth = $this->getReadConnection()->query($sql);
        $list = array();
        while ($row = $sth->fetch(\PDO::FETCH_ASSOC)) {
            array_push($list, array_values($row));
        }

        $filename = 'apdata.csv';

        $fp = fopen('php://output', 'w');

        fputcsv($fp, array('aid,bid,pm25,co,o3,no2,so2,temp,lat,lng,timestamp'), '"', chr(0));
        foreach ($list as $ferow) {
            fputcsv($fp, $ferow);
        }
        fclose($fp);

        return $filename;
    }

    public function insertAPDataOfBoard($bid, $data)
    {
        $sql = 'INSERT INTO APData (`board_id`, `no2`, `o3`, `co`, `so2`, `pm25`, `temperature`, `latitude`, `longitude`, `apdata_timestamp`)
                VALUES (:board_id, :no2, :o3, :co, :so2, :pm25, :temperature, :latitude, :longitude, :apdata_timestamp)';

        $sth = $this->getReadConnection()->prepare($sql);
        $sth->bindParam(':board_id', $bid, FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':no2', $data['no2'], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':o3', $data['o3'], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':co', $data['co'], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':so2', $data['so2'], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':pm25', $data['pm25'], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':temperature', $data['temperature'], FILTER_SANITIZE_NUMBER_FLOAT);
        $sth->bindParam(':latitude', $data['latitude'], FILTER_SANITIZE_NUMBER_FLOAT);
        $sth->bindParam(':longitude', $data['longitude'], FILTER_SANITIZE_NUMBER_FLOAT);
        $sth->bindParam(':apdata_timestamp', $data['apdata_timestamp'], FILTER_SANITIZE_NUMBER_INT|FILTER_SANITIZE_SPECIAL_CHARS); // have to chage the check filter
        $sth->execute();

        return $this->getReadConnection()->lastInsertId();
    }

    public function insertAPDataOfBoardWithRow($row)
    {
        $sql = 'insert into APData (`board_id`, `pm25`, `co`, `o3`, `no2`, `so2`, `temperature`, `latitude`, `longitude`, `apdata_timestamp`)
                VALUES (:board_id, :pm25, :co, :o3, :no2, :so2, :temperature, :latitude, :longitude, :apdata_timestamp)';

        $sth = $this->getReadConnection()->prepare($sql);
        $sth->bindParam(':board_id', $row[0], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':pm25', $row[1], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':co', $row[2], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':o3', $row[3], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':no2', $row[4], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':so2', $row[5], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':temperature', $row[6], FILTER_SANITIZE_NUMBER_FLOAT);
        $sth->bindParam(':latitude', $row[7], FILTER_SANITIZE_NUMBER_FLOAT);
        $sth->bindParam(':longitude', $row[8], FILTER_SANITIZE_NUMBER_FLOAT);
        $sth->bindParam(':apdata_timestamp', $row[9], FILTER_SANITIZE_NUMBER_INT|FILTER_SANITIZE_SPECIAL_CHARS); // have to chage the check filter
        $sth->execute();

        return $this->getReadConnection()->lastInsertId();
    }

    public function getAQIData($fields, $offset = false, $limit = false, $interlt = false, $startDate = false, $endDate = false, $lat = false, $lng = false, $orderby = false)
    {
        if ($fields && is_array($fields)) {
            foreach ($fields as $key => $field) {
                $fields[$key] = "`" . str_replace("`", "``", $field) . "`";
            }
            unset($key, $field);
            $fieldsStr = join(',', $fields);
        } else {
            $fieldsStr = 'AQIData.aqidata_id, AQIData.board_id, AQIData.no2, AQIData.o3, AQIData.co, AQIData.so2, AQIData.pm25, AQIData.latitude, AQIData.longitude, AQIData.aqidata_timestamp';
        }

        $sql = 'SELECT ' .$fieldsStr. '
                FROM AQIData';

        if ($interlt) {
            $sql .= ' WHERE `aqidata_timestamp` > now() - interval ' . $interlt . ' hour';
        } else {
            if ($startDate && $endDate) {
                $sql .= ' WHERE aqidata_timestamp between FROM_UNIXTIME('.$startDate.') and FROM_UNIXTIME('.$endDate.') ';
                if ($lat && $lng) {
                    $sql .= ' AND POINT(latitude, longitude) = POINT(:lat, :lng)';
                }
            } else {
                if ($lat && $lng) {
                    $sql .= ' WHERE POINT(latitude, longitude) = POINT(:lat, :lng)';
                }
            }
        }

        if ($orderby)
            $sql .= ' ORDER BY AQIData.aqidata_timestamp DESC';

        if ($limit)
            $sql .= ' LIMIT ' . (int)$limit;

        if ($offset)
            $sql .= ' OFFSET ' . (int)$offset;

        $sth = $this->getReadConnection()->prepare($sql);

        if ($lat && $lng) {
            $sth->bindParam(':lat', $lat, FILTER_SANITIZE_NUMBER_FLOAT);
            $sth->bindParam(':lng', $lng, FILTER_SANITIZE_NUMBER_FLOAT);
        }

        $sth->execute();
        $sth->setFetchMode(\PDO::FETCH_ASSOC);

        return $sth->fetchAll();
    }

    public function getAQIDataCSV($offset = false, $limit = false, $interlt = false, $startDate = false, $endDate = false, $lat = false, $lng = false, $orderby = false)
    {
        $sql = 'SELECT AQIData.aqidata_id, AQIData.board_id, AQIData.pm25, AQIData.co, AQIData.o3, AQIData.no2, AQIData.so2, AQIData.latitude, AQIData.longitude, AQIData.aqidata_timestamp
                FROM AQIData';

        if ($interlt)
            $sql .= ' WHERE `aqidata_timestamp` > NOW() - INTERVAL '. $interlt . ' HOUR';
        else {
            if ($startDate && $endDate) {
                $sql .= ' WHERE aqidata_timestamp between FROM_UNIXTIME('.$startDate.') and FROM_UNIXTIME('.$endDate.') ';
                if ($lat && $lng) {
                    $sql .= ' AND POINT(latitude, longitude) = POINT(:lat, :lng)';
                }
            } else {
                if ($lat && $lng) {
                    $sql .= ' WHERE POINT(latitude, longitude) = POINT(:lat, :lng)';
                }
            }
        }

        if ($orderby)
            $sql .= ' ORDER BY AQIData.aqidata_timestamp DESC';

        if ($limit)
            $sql .= ' LIMIT ' . (int)$limit;

        if ($offset)
            $sql .= ' OFFSET ' . (int)$offset;

        $sth = $this->getReadConnection()->prepare($sql);

        if ($lat && $lng) {
            $sth->bindParam(':lat', $lat, FILTER_SANITIZE_NUMBER_FLOAT);
            $sth->bindParam(':lng', $lng, FILTER_SANITIZE_NUMBER_FLOAT);
        }

        $sth->execute();
        $list = array();
        while ($row = $sth->fetch(\PDO::FETCH_ASSOC)) {
            array_push($list, array_values($row));
        }

        $filename = 'aqidata.csv';

        $fp = fopen('php://output', 'w');

        fputcsv($fp, array('aid,bid,pm25,co,o3,no2,so2,lat,lng,timestamp'), '"', chr(0));
        foreach ($list as $ferow) {
            fputcsv($fp, $ferow);
        }
        fclose($fp);

        return $filename;
    }

    public function getDistinctAQIDataCSV($offset = false, $limit = false, $orderby = false)
    {
        $sql = 'select * from (select * from AQIData order by aqidata_timestamp desc) as orderedAQI group by board_id';

        if ($orderby)
            $sql .= ' order by aqidata_timestamp desc';

        if ($limit)
            $sql .= ' LIMIT ' . (int)$limit;

        if ($offset)
            $sql .= ' OFFSET ' . (int)$offset;

        $sth = $this->getReadConnection()->prepare($sql);
        $sth->execute();
        $list = array();
        while ($row = $sth->fetch(\PDO::FETCH_ASSOC)) {
            array_push($list, array_values($row));
        }

        $filename = 'aqidata.csv';

        $fp = fopen('php://output', 'w');
        fputcsv($fp, array('aid,bid,pm25,co,o3,no2,so2,lat,lng,timestamp'), '"', chr(0));
        foreach ($list as $ferow) {
            fputcsv($fp, $ferow);
        }
        fclose($fp);

        return $filename;
    }

    public function getDistinctLatLngAQIDataCSV($offset = false, $limit = false, $startDate = false, $endDate = false, $orderby = false)
    {
        $sql = 'select * from (select * from AQIData order by aqidata_timestamp desc) as orderedAQI group by latitude, longitude';

        if ($startDate && $endDate) {
            $sql .= ' HAVING orderedAQI.aqidata_timestamp between FROM_UNIXTIME(' . $startDate . ') and FROM_UNIXTIME(' . $endDate . ') ';
        }

        if ($orderby)
            $sql .= ' order by aqidata_timestamp desc';

        if ($limit)
            $sql .= ' LIMIT ' . (int)$limit;

        if ($offset)
            $sql .= ' OFFSET ' . (int)$offset;

        $sth = $this->getReadConnection()->prepare($sql);
        $sth->execute();
        $list = array();
        while ($row = $sth->fetch(\PDO::FETCH_ASSOC)) {
            array_push($list, array_values($row));
        }

        $filename = 'aqidata.csv';

        $fp = fopen('php://output', 'w');
        fputcsv($fp, array('aid,bid,pm25,co,o3,no2,so2,lat,lng,timestamp'), '"', chr(0));
        foreach ($list as $ferow) {
            fputcsv($fp, $ferow);
        }
        fclose($fp);

        return $filename;
    }

    public function insertAQIDataOfBoard($id, $data)
    {
        $sql = 'INSERT INTO AQIData (`board_id`, `no2`, `o3`, `co`, `so2`, `pm25`, `latitude`, `longitude`, `aqidata_timestamp`)
                VALUES (:board_id, :no2, :o3, :co, :so2, :pm25, :latitude, :longitude, :aqidata_timestamp)';

        $sth = $this->getReadConnection()->prepare($sql);
        $sth->bindParam(':board_id', $id, FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':no2', $data['no2'], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':o3', $data['o3'], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':co', $data['co'], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':so2', $data['so2'], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':pm25', $data['pm25'], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':latitude', $data['latitude'], FILTER_SANITIZE_NUMBER_FLOAT);
        $sth->bindParam(':longitude', $data['longitude'], FILTER_SANITIZE_NUMBER_FLOAT);
//        $sth->bindParam(':aqldata_timestamp', $data['hrdata_timestamp'], FILTER_VALIDATE_REGEXP, array('options') => array('regexp' => '/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}/'));
        $sth->bindParam(':aqidata_timestamp', $data['aqidata_timestamp'], FILTER_SANITIZE_NUMBER_INT|FILTER_SANITIZE_SPECIAL_CHARS); // have to chage the check filter
        $sth->execute();

        return $this->getReadConnection()->lastInsertId();
    }

    public function insertAQIDataOfBoardWithRow($row)
    {
        $sql = 'insert into AQIData (`board_id`, `no2`, `o3`, `co`, `so2`, `pm25`, `latitude`, `longitude`, `aqidata_timestamp`)
                VALUES (:board_id, :no2, :o3, :co, :so2, :pm25, :latitude, :longitude, :aqidata_timestamp)';

        $sth = $this->getReadConnection()->prepare($sql);
        $sth->bindParam(':board_id', $row[0], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':pm25', $row[1], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':co', $row[2], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':o3', $row[3], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':no2', $row[4], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':so2', $row[5], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':latitude', $row[6], FILTER_SANITIZE_NUMBER_FLOAT);
        $sth->bindParam(':longitude', $row[7], FILTER_SANITIZE_NUMBER_FLOAT);
        $sth->bindParam(':aqidata_timestamp', $row[8], FILTER_SANITIZE_NUMBER_INT|FILTER_SANITIZE_SPECIAL_CHARS); // have to chage the check filter
        $sth->execute();

        return $this->getReadConnection()->lastInsertId();
    }

    public function getBoardAQIData($bid, $fields, $offset = false, $limit = false, $interlt = false, $startDate = false, $endDate = false, $lat = false, $lng = false, $orderby = false)
    {
        if ($fields && is_array($fields)) {
            foreach ($fields as $key => $field) {
                $fields[$key] = "`" . str_replace("`", "``", $field) . "`";
            }
            unset($key, $field);
            $fieldsStr = join(',', $fields);
        } else {
            $fieldsStr = 'AQIData.aqidata_id, AQIData.board_id, AQIData.no2, AQIData.o3, AQIData.co, AQIData.so2, AQIData.pm25, AQIData.latitude, AQIData.longitude, AQIData.aqidata_timestamp';
        }

        $sql = 'SELECT ' .$fieldsStr. '
                FROM AQIData, Board WHERE Board.board_id = AQIData.board_id AND Board.board_id = :bid';

        if ($interlt) {
            $sql .= ' AND `aqidata_timestamp` > now() - interval ' . $interlt . ' hour';
        } else {
            if ($startDate && $endDate) {
                $sql .= ' AND aqidata_timestamp between FROM_UNIXTIME('.$startDate.') and FROM_UNIXTIME('.$endDate.') ';
            }

            if ($lat && $lng) {
                $sql .= ' AND POINT(latitude, longitude) = POINT(:lat, :lng)';
            }
        }

        if ($orderby)
            $sql .= ' ORDER BY AQIData.aqidata_timestamp DESC';

        if ($limit)
            $sql .= ' LIMIT ' . (int)$limit;

        if ($offset)
            $sql .= ' OFFSET ' . (int)$offset;

        $sth = $this->getReadConnection()->prepare($sql);
        $sth->bindParam(':bid', $bid, FILTER_SANITIZE_NUMBER_INT);
        if ($lat && $lng) {
            $sth->bindParam(':lat', $lat, FILTER_SANITIZE_NUMBER_FLOAT);
            $sth->bindParam(':lng', $lng, FILTER_SANITIZE_NUMBER_FLOAT);
        }

        $sth->execute();
        $sth->setFetchMode(\PDO::FETCH_ASSOC);

        return $sth->fetchAll();
    }

    public function getBoardAQIDataCSV($bid, $offset = false, $limit = false, $interlt = false, $startDate = false, $endDate = false, $lat = false, $lng = false, $orderby = false)
    {
        $sql = 'SELECT AQIData.aqidata_id, AQIData.board_id, AQIData.pm25, AQIData.co, AQIData.o3, AQIData.no2, AQIData.so2, AQIData.latitude, AQIData.longitude, AQIData.aqidata_timestamp
                FROM AQIData, Board WHERE AQIData.board_id = Board.board_id = AND Board.board_id = :bid';

        if ($interlt)
            $sql .= ' WHERE `aqidata_timestamp` > NOW() - INTERVAL '. $interlt . ' HOUR';
        else {
            if ($startDate && $endDate) {
                $sql .= ' WHERE aqidata_timestamp between FROM_UNIXTIME('.$startDate.') and FROM_UNIXTIME('.$endDate.') ';
                if ($lat && $lng) {
                    $sql .= ' AND POINT(latitude, longitude) = POINT(:lat, :lng)';
                }
            }

            if ($lat && $lng) {
                $sql .= ' AND POINT(latitude, longitude) = POINT(:lat, :lng)';
            }
        }

        if ($orderby)
            $sql .= ' ORDER BY AQIData.aqidata_timestamp DESC';

        if ($limit)
            $sql .= ' LIMIT ' . (int)$limit;

        if ($offset)
            $sql .= ' OFFSET ' . (int)$offset;

        $sth = $this->getReadConnection()->prepare($sql);
        $sth->bindParam(':bid', $bid, FILTER_SANITIZE_NUMBER_INT);
        if ($lat && $lng) {
            $sth->bindParam(':lat', $lat, FILTER_SANITIZE_NUMBER_FLOAT);
            $sth->bindParam(':lng', $lng, FILTER_SANITIZE_NUMBER_FLOAT);
        }

        $sth->execute();
        $list = array();
        while ($row = $sth->fetch(\PDO::FETCH_ASSOC)) {
            array_push($list, array_values($row));
        }

        $filename = 'aqidata.csv';

        $fp = fopen('php://output', 'w');

        fputcsv($fp, array('aid,bid,pm25,co,o3,no2,so2,lat,lng,timestamp'), '"', chr(0));
        foreach ($list as $ferow) {
            fputcsv($fp, $ferow);
        }
        fclose($fp);

        return $filename;
    }
}
