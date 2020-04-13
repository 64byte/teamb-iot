<?php
namespace Iot\Model;

use Slimvc\Core\Model;

class UserModel extends Model
{
    public function insertUser($data = array())
    {
        $sql = 'INSERT INTO User (`email`, `password`, `fname`, `lname`, `birthday`, `gender`)
                VALUES (:email, :password, :fname, :lname, :birthday, :gender)';

        $birthday = $data['birthday-year'].'-'.$data['birthday-month'].'-'.$data['birthday-day'];

        $sth = $this->getReadConnection()->prepare($sql);
        $sth->bindParam(':email', $data['email'], FILTER_SANITIZE_EMAIL);
        $sth->bindParam(':password', $data['password'], FILTER_SANITIZE_STRING);
        $sth->bindParam(':fname', $data['fname'], FILTER_SANITIZE_STRING);
        $sth->bindParam(':lname', $data['lname'], FILTER_SANITIZE_STRING);
        $sth->bindParam(':birthday', $birthday, FILTER_SANITIZE_SPECIAL_CHARS|FILTER_SANITIZE_NUMBER_INT); // have to chage the check filter
        $sth->bindParam(':gender', $data['gender'], FILTER_SANITIZE_NUMBER_INT);
        $sth->execute();

        return $this->getReadConnection()->lastInsertId();
    }

    public function getAllUsers($fields = array(), $offset = 0, $limit = 10)
    {
        // TODO: we just using PDO for example here, a DAL(Database Access Layer) is strongly recommended

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

        $sql = 'SELECT ' . $fieldsStr . ' FROM `programmers`';

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
    public function getUser($id, $fields = array())
    {
        if ($fields && is_array($fields)) {
            foreach ($fields as $key => $field) {
                $fields[$key] = "`" . str_replace("`", "``", $field) . "`";
            }
            unset($key, $field);
            $fieldsStr = join(',', $fields);
        } else {
            $fieldsStr = '*';
        }

        $sql = 'SELECT ' . $fieldsStr . ' FROM `User` WHERE user_id = :id LIMIT 1';
        $sth = $this->getReadConnection()->prepare($sql);
        $sth->bindParam(':id', $id, FILTER_SANITIZE_NUMBER_INT);
        $sth->setFetchMode(\PDO::FETCH_ASSOC);
        $sth->execute(array(intval($id)));

        return $sth->fetch();
    }

    public function getUserWithEmail($email, $fields = array())
    {
        if ($fields && is_array($fields)) {
            foreach ($fields as $key => $field) {
                $fields[$key] = "`" . str_replace("`", "``", $field) . "`";
            }
            unset($key, $field);
            $fieldsStr = join(',', $fields);
        } else {
            $fieldsStr = '*';
        }

        $sql = 'SELECT ' . $fieldsStr . ' FROM `User` WHERE email = :email LIMIT 1';
        $sth = $this->getReadConnection()->prepare($sql);
        $sth->bindParam(':email', $email, FILTER_SANITIZE_EMAIL);
        $sth->setFetchMode(\PDO::FETCH_ASSOC);
        $sth->execute(array($email));

        return $sth->fetch();
    }

    public function updateUserActiveWithEmail($email, $value) {
        $sql = 'UPDATE User SET active = :active WHERE email = :email';

        $sth = $this->getReadConnection()->prepare($sql);
        $sth->bindParam(':email', $email, FILTER_SANITIZE_EMAIL);
        $sth->bindParam(':active', $value, FILTER_SANITIZE_NUMBER_INT);

        return $sth->execute();
    }

    public function updateUserPwdActiveWithEmail($email, $value) {
     //   var_dump($email);
     //   var_dump($value);

        $sql = 'UPDATE User SET pwdactive = :active WHERE email = :email';

        $sth = $this->getReadConnection()->prepare($sql);
        $sth->bindParam(':email', $email, FILTER_SANITIZE_EMAIL);
        $sth->bindParam(':active', $value, FILTER_SANITIZE_NUMBER_INT);

        return $sth->execute();
    }

    public function updateUserPassword($id, $value)
    {
        $sql = 'UPDATE User SET password = :password WHERE user_id = :id';

        $sth = $this->getReadConnection()->prepare($sql);
        $sth->bindParam(':id', $id, FILTER_VALIDATE_INT);
        $sth->bindParam(':password', $value, FILTER_SANITIZE_STRING);

        return $sth->execute();
    }

    public function updateUserPasswordWithEmail($email, $value)
    {
    //    var_dump($email)var_dump($value);

        $sql = 'UPDATE User SET password = :password WHERE email = :email';

        $sth = $this->getReadConnection()->prepare($sql);
        $sth->bindParam(':email', $email, FILTER_SANITIZE_EMAIL);
        $sth->bindParam(':password', $value, FILTER_SANITIZE_STRING);

        return $sth->execute();
    }

    public function deleteUser($id) {
        $sql = 'DELETE FROM User WHERE user_id = :id';

        $sth = $this->getReadConnection()->prepare($sql);
        $sth->bindParam(':id', $id, FILTER_VALIDATE_INT);

        return $sth->execute();
    }

    public function insertBoardOfUser($id, $mac_addr, $name)
    {
        $sql = 'INSERT INTO Board (`user_id`, `mac_address`, `name`)
                VALUES (:user_id, :mac_address, :name)';

        $sth = $this->getReadConnection()->prepare($sql);
        $sth->bindParam(':user_id', $id, FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':mac_address', $mac_addr, FILTER_SANITIZE_STRING);
        $sth->bindParam(':name', $name, FILTER_SANITIZE_STRING);
        $sth->execute();

        return $this->getReadConnection()->lastInsertId();
    }

    public function getAllBoardsOfUser($id, $fields = array(), $offset = false, $limit = false, $orderby = false)
    {
        if ($fields && is_array($fields)) {
            foreach ($fields as $key => $field) {
                $fields[$key] = "`" . str_replace("`", "``", $field) . "`";
            }
            unset($key, $field);
            $fieldsStr = join(',', $fields);
        } else {
            $fieldsStr = 'board_id, Board.user_id, mac_address, name';
        }

        $sql = 'SELECT ' . $fieldsStr . ' FROM User, Board WHERE User.user_id=Board.user_id AND User.user_id=:id';

        if ($orderby)
            $sql .= ' ORDER BY board_id DESC';

        if ($limit)
            $sql .= ' LIMIT ' . (int)$limit;

        if ($offset)
            $sql .= ' OFFSET ' . (int)$offset;

        $sth = $this->getReadConnection()->prepare($sql);
        $sth->bindParam(':id', $id, FILTER_SANITIZE_NUMBER_INT);
        $sth->execute();
        $sth->setFetchMode(\PDO::FETCH_ASSOC);

        return $sth->fetchAll();
    }

    public function getAllHRDataOfUser($id, $fields = array(), $offset = false, $limit = false, $startDate = false, $endDate = false, $orderby = false)
    {
        if ($fields && is_array($fields)) {
            foreach ($fields as $key => $field) {
                $fields[$key] = "`" . str_replace("`", "``", $field) . "`";
            }
            unset($key, $field);
            $fieldsStr = join(',', $fields);
        } else {
            $fieldsStr = 'HRData.hrdata_id, User.user_id, HRData.heartrate, HRData.rr_interval, HRData.latitude, HRData.longitude, HRData.hrdata_timestamp';
        }

        $sql = 'SELECT ' . $fieldsStr . ' FROM User, HRData WHERE User.user_id=HRData.user_id AND User.user_id=:id';

        if ($startDate && $endDate) {
            $sql .= ' and hrdata_timestamp between FROM_UNIXTIME('.$startDate.') and FROM_UNIXTIME('.$endDate.') ';
        }

        if ($orderby)
            $sql .= ' ORDER BY HRData.hrdata_timestamp DESC';

        if ($limit)
            $sql .= ' LIMIT ' . (int)$limit;

        if ($offset)
            $sql .= ' OFFSET ' . (int)$offset;

        $sth = $this->getReadConnection()->prepare($sql);
        $sth->bindParam(':id', $id, FILTER_SANITIZE_NUMBER_INT);
        $sth->execute();
        $sth->setFetchMode(\PDO::FETCH_ASSOC);

        return $sth->fetchAll();
    }

    public function getAllHRDataOfUserCSV($id, $offset = false, $limit = false, $startDate = false, $endDate = false, $orderby = false)
    {
        $sql = 'SELECT HRData.hrdata_id, User.user_id, HRData.heartrate, HRData.rr_interval, HRData.latitude, HRData.longitude, HRData.hrdata_timestamp FROM User, HRData WHERE User.user_id=HRData.user_id AND User.user_id=:id';

        if ($startDate && $endDate) {
            $sql .= ' and hrdata_timestamp between FROM_UNIXTIME('.$startDate.') and FROM_UNIXTIME('.$endDate.') ';
        }

        if ($orderby)
            $sql .= ' ORDER BY HRData.hrdata_timestamp DESC';

        if ($limit)
            $sql .= ' LIMIT ' . (int)$limit;

        if ($offset)
            $sql .= ' OFFSET ' . (int)$offset;

        $sth = $this->getReadConnection()->prepare($sql);
        $sth->bindParam(':id', $id, FILTER_SANITIZE_NUMBER_INT);
        $sth->execute();

        $list = array();
        while ($row = $sth->fetch(\PDO::FETCH_ASSOC)) {
            array_push($list, array_values($row));
        }

        $filename = 'hrdata.csv';

        $fp = fopen('php://output', 'w');

        fputcsv($fp, array('hid,uid,hr,rri,lat,lng,timestamp'), '"', chr(0));
        foreach ($list as $ferow) {
            fputcsv($fp, $ferow);
        }
        fclose($fp);

        return $filename;
    }

    public function insertHRDataOfUser($id, $data = array())
    {
        $sql = 'INSERT INTO HRData (`user_id`, `heartrate`, `rr_interval`, `latitude`, `longitude`, `hrdata_timestamp`)
                VALUES (:user_id, :heartrate, :rr_interval, :latitude, :longitude, :hrdata_timestamp)';

        $sth = $this->getReadConnection()->prepare($sql);
        $sth->bindParam(':user_id', $id, FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':heartrate', $data['heartrate'], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':rr_interval', $data['rr_interval'], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':latitude', $data['latitude'], FILTER_SANITIZE_NUMBER_FLOAT);
        $sth->bindParam(':longitude', $data['longitude'], FILTER_SANITIZE_NUMBER_FLOAT);
        $sth->bindParam(':hrdata_timestamp', $data['hrdata_timestamp'], FILTER_SANITIZE_NUMBER_INT|FILTER_SANITIZE_SPECIAL_CHARS); // have to chage the check filter
        $sth->execute();

        return $this->getReadConnection()->lastInsertId();
    }

    public function insertHRDataOfUserRow($row)
    {
        $sql = 'INSERT INTO HRData (`user_id`, `heartrate`, `rr_interval`, `latitude`, `longitude`, `hrdata_timestamp`)
                VALUES (:user_id, :heartrate, :rr_interval, :latitude, :longitude, :hrdata_timestamp)';

        $sth = $this->getReadConnection()->prepare($sql);
        $sth->bindParam(':user_id', $row[0], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':heartrate', $row[1], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':rr_interval', $row[2], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':latitude', $row[3], FILTER_SANITIZE_NUMBER_FLOAT);
        $sth->bindParam(':longitude', $row[4], FILTER_SANITIZE_NUMBER_FLOAT);
        $sth->bindParam(':hrdata_timestamp', $row[5], FILTER_SANITIZE_NUMBER_INT|FILTER_SANITIZE_SPECIAL_CHARS); // have to chage the check filter
        $sth->execute();

        return $this->getReadConnection()->lastInsertId();
    }

    public function getAQIDataOfAllBoardsOfUser($id, $fields = array(), $offset = false, $limit = false, $startDate = false, $endDate = false, $lat = false, $lng = false, $orderby = false)
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

        $sql = 'SELECT ' . $fieldsStr . ' FROM User, Board, AQIData WHERE User.user_id = Board.user_id AND Board.board_id = AQIData.board_id AND User.user_id ='.$id;

        if ($startDate && $endDate) {
            $sql .= ' AND aqidata_timestamp between FROM_UNIXTIME('.$startDate.') and FROM_UNIXTIME('.$endDate.') ';
        }

        if ($lat && $lng) {
            $sql .= ' AND POINT(latitude, longitude) = POINT(:lat, :lng)';
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

    public function getAQIDataOfAllBoardsOfUserCSV($id, $offset = false, $limit = false, $startDate = false, $endDate = false, $lat = false, $lng = false, $orderby = false)
    {
        $sql = 'SELECT AQIData.aqidata_id, AQIData.board_id, AQIData.pm25, AQIData.co, AQIData.o3, AQIData.no2, AQIData.so2, AQIData.latitude, AQIData.longitude, AQIData.aqidata_timestamp FROM User, Board, AQIData WHERE User.user_id = Board.user_id AND Board.board_id = AQIData.board_id AND User.user_id =:id';

        if ($startDate && $endDate) {
            $sql .= ' AND aqidata_timestamp between FROM_UNIXTIME('.$startDate.') and FROM_UNIXTIME('.$endDate.') ';
        }

        if ($lat && $lng) {
            $sql .= ' AND POINT(latitude, longitude) = POINT(:lat, :lng)';
        }

        if ($orderby)
            $sql .= ' ORDER BY AQIData.aqidata_timestamp DESC';

        if ($limit)
            $sql .= ' LIMIT ' . (int)$limit;

        if ($offset)
            $sql .= ' OFFSET ' . (int)$offset;

        $sth = $this->getReadConnection()->prepare($sql);
        $sth->bindParam(':id', $id, FILTER_SANITIZE_NUMBER_INT);

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

    public function getAQIDataOfBoardsOfUser($id, $bid, $fields = array(), $offset = false, $limit = false, $startDate = false, $endDate = false, $lat = false, $lng = false, $orderby = false)
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

        $sql = 'SELECT ' . $fieldsStr . ' FROM User, Board, AQIData WHERE User.user_id = Board.user_id AND Board.board_id = AQIData.board_id AND User.user_id='.$id.' AND Board.board_id='.$bid;

        if ($startDate && $endDate) {
            $sql .= ' AND aqidata_timestamp between FROM_UNIXTIME('.$startDate.') and FROM_UNIXTIME('.$endDate.') ';
        }

        if ($lat && $lng) {
            $sql .= ' AND POINT(latitude, longitude) = POINT(:lat, :lng)';
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

    public function getAQIDataOfBoardsOfUserCSV($id, $board_id, $offset = false, $limit = false, $startDate = false, $endDate = false, $lat = false, $lng = false, $orderby = false)
    {
        $sql = 'SELECT AQIData.aqidata_id, AQIData.board_id, AQIData.pm25, AQIData.co, AQIData.o3, AQIData.no2, AQIData.so2, AQIData.latitude, AQIData.longitude, AQIData.aqidata_timestamp
                FROM User, Board, AQIData WHERE User.user_id = Board.user_id AND Board.board_id = AQIData.board_id AND User.user_id=:id AND Board.board_id=:board_id';

        if ($startDate && $endDate) {
            $sql .= ' AND aqidata_timestamp between FROM_UNIXTIME('.$startDate.') and FROM_UNIXTIME('.$endDate.') ';
        }

        if ($lat && $lng) {
            $sql .= ' AND POINT(latitude, longitude) = POINT(:lat, :lng)';
        }

        if ($orderby)
            $sql .= ' ORDER BY AQIData.aqidata_timestamp DESC';

        if ($limit)
            $sql .= ' LIMIT ' . (int)$limit;

        if ($offset)
            $sql .= ' OFFSET ' . (int)$offset;

        $sth = $this->getReadConnection()->prepare($sql);
        $sth->bindParam(':id', $id, FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':board_id', $board_id, FILTER_SANITIZE_NUMBER_INT);

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

    public function insertAQIDataOfBoardOfUser($id, $bid, $data)
    {
        $sql = 'insert into AQIData (`board_id`, `pm25`, `co`, `o3`, `no2`, `so2`, `latitude`, `longitude`, `aqidata_timestamp`)
                select :board_id, :pm25, :co, :o3, :no2, :so2, :latitude, :longitude, :aqidata_timestamp
	            from dual where exists (select Board.board_id from User, Board where User.user_id = Board.user_id and User.user_id =:id)';

        $sth = $this->getReadConnection()->prepare($sql);
        $sth->bindParam(':id', $id, FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':board_id', $bid, FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':no2', $data['no2'], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':o3', $data['o3'], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':co', $data['co'], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':so2', $data['so2'], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':pm25', $data['pm25'], FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':latitude', $data['latitude'], FILTER_SANITIZE_NUMBER_FLOAT);
        $sth->bindParam(':longitude', $data['longitude'], FILTER_SANITIZE_NUMBER_FLOAT);
        $sth->bindParam(':aqidata_timestamp', $data['aqidata_timestamp'], FILTER_SANITIZE_NUMBER_INT|FILTER_SANITIZE_SPECIAL_CHARS); // have to chage the check filter
        $sth->execute();

        return $this->getReadConnection()->lastInsertId();
    }

    public function getAPDataOfAllBoardsOfUser($id, $fields = array(), $offset = false, $limit = false, $startDate = false, $endDate = false, $lat = false, $lng = false, $orderby = false)
    {
        if ($fields && is_array($fields)) {
            foreach ($fields as $key => $field) {
                $fields[$key] = "`" . str_replace("`", "``", $field) . "`";
            }
            unset($key, $field);
            $fieldsStr = join(',', $fields);
        } else {
            $fieldsStr = 'APData.apdata_id, APData.board_id, APData.no2, APData.o3, APData.co, APData.so2, APData.pm25, APData.latitude, APData.longitude, APData.apdata_timestamp';
        }

        $sql = 'SELECT ' . $fieldsStr . ' FROM User, Board, APData WHERE User.user_id = Board.user_id AND Board.board_id = APData.board_id AND User.user_id ='.$id;

        if ($startDate && $endDate) {
            $sql .= ' AND apdata_timestamp between FROM_UNIXTIME('.$startDate.') and FROM_UNIXTIME('.$endDate.') ';
        }

        if ($lat && $lng) {
            $sql .= ' AND POINT(latitude, longitude) = POINT(:lat, :lng)';
        }

        if ($orderby)
            $sql .= ' ORDER BY APData.apdata_timestamp DESC';

        if ($limit)
            $sql .= ' LIMIT ' . (int)$limit;

        if ($offset)
            $sql .= ' OFFSET ' . (int)$offset;

        $sth = $this->getReadConnection()->prepare($sql);

        if ($lat && $lng) {
            $sth->bindParam(':lat', $lat, FILTER_SANITIZE_NUMBER_FLOAT);
            $sth->bindParam(':lng', $lng, FILTER_SANITIZE_NUMBER_FLOAT);
        }

        $sth->exectue();
        $sth->setFetchMode(\PDO::FETCH_ASSOC);

        return $sth->fetchAll();
    }

    public function getAPDataOfAllBoardsOfUserCSV($id, $offset = false, $limit = false, $startDate = false, $endDate = false, $lat = false, $lng = false, $orderby = false)
    {
        $sql = 'SELECT APData.apdata_id, APData.board_id, APData.pm25, APData.co, APData.o3, APData.no2, APData.so2, APData.temperature, APData.latitude, APData.longitude, APData.apdata_timestamp FROM User, Board, APData WHERE User.user_id = Board.user_id AND Board.board_id = APData.board_id AND User.user_id =:id';

        if ($startDate && $endDate) {
            $sql .= ' AND apdata_timestamp between FROM_UNIXTIME('.$startDate.') and FROM_UNIXTIME('.$endDate.') ';
        }

        if ($lat && $lng) {
            $sql .= ' AND POINT(latitude, longitude) = POINT(:lat, :lng)';
        }

        if ($orderby)
            $sql .= ' ORDER BY APData.apdata_timestamp DESC';

        if ($limit)
            $sql .= ' LIMIT ' . (int)$limit;

        if ($offset)
            $sql .= ' OFFSET ' . (int)$offset;

        $sth = $this->getReadConnection()->prepare($sql);
        $sth->bindParam(':id', $id, FILTER_SANITIZE_NUMBER_INT);

        if ($lat && $lng) {
            $sth->bindParam(':lat', $lat, FILTER_SANITIZE_NUMBER_FLOAT);
            $sth->bindParam(':lng', $lng, FILTER_SANITIZE_NUMBER_FLOAT);
        }

        $sth->execute();

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

    public function getAPDataOfBoardsOfUser($id, $bid, $fields = array(), $offset = false, $limit = false,  $startDate = false, $endDate = false, $lat = false, $lng = false, $orderby = false)
    {
        if ($fields && is_array($fields)) {
            foreach ($fields as $key => $field) {
                $fields[$key] = "`" . str_replace("`", "``", $field) . "`";
            }
            unset($key, $field);
            $fieldsStr = join(',', $fields);
        } else {
            $fieldsStr = 'APData.apdata_id, APData.board_id, APData.no2, APData.o3, APData.co, APData.so2, APData.pm25, APData.temperature, APData.latitude, APData.longitude, APData.apdata_timestamp';
        }

        $sql = 'SELECT ' . $fieldsStr . ' FROM User, Board, APData WHERE User.user_id = Board.user_id AND Board.board_id = APData.board_id AND User.user_id=:id AND Board.board_id=:bid';

        if ($startDate && $endDate) {
            $sql .= ' AND apdata_timestamp between FROM_UNIXTIME('.$startDate.') and FROM_UNIXTIME('.$endDate.') ';
        }

        if ($lat && $lng) {
            $sql .= ' AND POINT(latitude, longitude) = POINT(:lat, :lng)';
        }


        if ($orderby)
            $sql .= ' ORDER BY APData.apdata_timestamp DESC';

        if ($limit)
            $sql .= ' LIMIT ' . (int)$limit;

        if ($offset)
            $sql .= ' OFFSET ' . (int)$offset;

        $sth = $this->getReadConnection()->prepare($sql);
        $sth->bindParam(':id', $id, FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':bid', $bid, FILTER_SANITIZE_NUMBER_INT);

        if ($lat && $lng) {
            $sth->bindParam(':lat', $lat, FILTER_SANITIZE_NUMBER_FLOAT);
            $sth->bindParam(':lng', $lng, FILTER_SANITIZE_NUMBER_FLOAT);
        }

        $sth->setFetchMode(\PDO::FETCH_ASSOC);

        return $sth->fetchAll();
    }

    public function getAPDataOfBoardsOfUserCSV($id, $board_id, $offset = false, $limit = false, $startDate = false, $endDate = false, $lat = false, $lng = false, $orderby = false)
    {
        $sql = 'SELECT APData.apdata_id, APData.board_id, APData.pm25, APData.co, APData.o3, APData.no2, APData.so2, APData.temperature, APData.latitude, APData.longitude, APData.apdata_timestamp
                FROM User, Board, APData WHERE User.user_id = Board.user_id AND Board.board_id = APData.board_id AND User.user_id=:id AND Board.board_id=:board_id';

        if ($startDate && $endDate) {
            $sql .= ' AND apdata_timestamp between FROM_UNIXTIME('.$startDate.') and FROM_UNIXTIME('.$endDate.') ';
        }

        if ($lat && $lng) {
            $sql .= ' AND POINT(latitude, longitude) = POINT(:lat, :lng)';
        }

        if ($orderby)
            $sql .= ' ORDER BY APData.apdata_timestamp DESC';

        if ($limit)
            $sql .= ' LIMIT ' . (int)$limit;

        if ($offset)
            $sql .= ' OFFSET ' . (int)$offset;

        $sth = $this->getReadConnection()->prepare($sql);
        $sth->bindParam(':id', $id, FILTER_SANITIZE_NUMBER_INT);
        $sth->bindParam(':board_id', $board_id, FILTER_SANITIZE_NUMBER_INT);

        if ($lat && $lng) {
            $sth->bindParam(':lat', $lat, FILTER_SANITIZE_NUMBER_FLOAT);
            $sth->bindParam(':lng', $lng, FILTER_SANITIZE_NUMBER_FLOAT);
        }

        $sth->execute();

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

    public function insertAPDataOfBoardOfUser($id, $bid, $data)
    {
        $sql = 'insert into APData (`board_id`, `no2`, `o3`, `co`, `so2`, `pm25`, `temperature`, `latitude`, `longitude`, `apdata_timestamp`)
                select :board_id, :no2, :o3, :co, :so2, :pm25, :temperature, :latitude, :longitude, :apdata_timestamp
	            from dual where exists (select Board.board_id from User, Board where User.user_id = Board.user_id and User.user_id =:id)';

        $sth = $this->getReadConnection()->prepare($sql);
        $sth->bindParam(':id', $id, FILTER_SANITIZE_NUMBER_INT);
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
}
