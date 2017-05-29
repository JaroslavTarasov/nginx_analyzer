<?php

namespace app\services;

use app\models\Logs;
use app\models\QueryTypes;
use app\models\UploadHistory;
use app\models\UserAgents;
use Yii;

/**
 * Class LogParser
 * @package app\services
 * @property LogParser $parser The parser component. This property is read-only.
 */
class LogParser
{
    /**
     * This method imports data from file into array $rows deleting
     * empty last cell of array
     *
     * @param string $path file for parsing
     * @return array array with every string in private cell
     */
    public function indexFile($path)
    {
        $file = file_get_contents($path);
        $rows = explode("\n", $file);
        array_pop($rows);
        return $rows;
    }

    /**
     * This method inserts data from incoming array into DB through UploadFile
     *
     * @param array $rows file with logs converted into array
     * @param int $file_id id of current uploaded file in table upload_history
     * @return bool returns 0 if succeeded
     */
    public function logUpload($rows, $file_id)
    {

        foreach ($rows as $row => $data) {
            preg_match_all('/"(?:\\\\.|[^\\\\"])*"|\S+/', $data, $matches);
            $string = $matches[0][5];
            $string = str_replace('"', '', $string);
            $row_data1 = explode(' ', $string);
            $row_data = $matches;

            $types = QueryTypes::find()->where(['query_type' => $row_data1[0]])->one();
            if (is_null($types)) {
                $types = new QueryTypes();
                $types->query_type = $row_data1[0];
                $types->save();
            }

            $useragents = UserAgents::find()->where(['browser_info' => str_replace('"', '', $row_data[0][10])])->one();
            if (is_null($useragents)) {
                $useragents = new UserAgents();
                $useragents->browser_info = str_replace('"', '', $row_data[0][10]);
                $useragents->save();
            }
            $logs = new Logs();
            $logs->query_type = $types->query_type_id;
            $logs->sip = $row_data[0][0];
            $logs->query_date = str_replace('[', '', $row_data[0][3]) . str_replace(']', '', $row_data[0][4]);
            if (substr_count($row_data1[1], '?') === 0) {
                $logs->url_query = $row_data1[1];
            } elseif (substr_count($row_data1[1], '?') > 0) {
                $logs->url_query = stristr($row_data1[1], '?', true);
            }
            $logs->query_code = $row_data[0][6];
            $logs->query_size = $row_data[0][7];
            $logs->query_time_float = $row_data[0][8];
            $logs->query_time_numeric = $row_data[0][8];
            $logs->quested_page = str_replace('"', '', $row_data[0][9]);
            if (str_replace('"', '', $row_data[0][11]) != "-") {
                $logs->user_ip = str_replace('"', '', $row_data[0][11]);
            } elseif (str_replace('"', '', $row_data[0][11]) === "-") {
                $logs->user_ip = $row_data[0][0];
            }
            $logs->uploaded_file = $file_id;
            $logs->browser_info = $useragents->user_agent_id;
            $logs->save();
        }
        Yii::$app->session->setFlash('success', "Файл добавлен в БД");
        return 0;
    }

    /**
     * This method inserts data from incoming array into DB through console
     *
     * @param array $rows file with logs converted into array
     * @param string $filename the name of the parsing file without path
     * @return bool returns 0 if succeeded
     */
    public function logUploadThroughConsole($rows, $filename)
    {

        $uploadedfile = new UploadHistory();
        $uploadedfile->filename = $filename;
        $uploadedfile->save();

        foreach ($rows as $row => $data) {
            preg_match_all('/"(?:\\\\.|[^\\\\"])*"|\S+/', $data, $matches);
            $string = $matches[0][5];
            $string = str_replace('"', '', $string);
            $row_data1 = explode(' ', $string);
            $row_data = $matches;

            $types = QueryTypes::find()->where(['query_type' => $row_data1[0]])->one();
            if (is_null($types)) {
                $types = new QueryTypes();
                $types->query_type = $row_data1[0];
                $types->save();
            }

            $useragents = UserAgents::find()->where(['browser_info' => str_replace('"', '', $row_data[0][10])])->one();
            if (is_null($useragents)) {
                $useragents = new UserAgents();
                $useragents->browser_info = str_replace('"', '', $row_data[0][10]);
                $useragents->save();
            }

            $logs = new Logs();
            $logs->query_type = $types->query_type_id;
            $logs->sip = $row_data[0][0];
            $logs->query_date = str_replace('[', '', $row_data[0][3]) . str_replace(']', '', $row_data[0][4]);
            if (substr_count($row_data1[1], '?') === 0) {
                $logs->url_query = $row_data1[1];
            } elseif (substr_count($row_data1[1], '?') > 0) {
                $logs->url_query = stristr($row_data1[1], '?', true);
            }
            $logs->query_code = $row_data[0][6];
            $logs->query_size = $row_data[0][7];
            $logs->query_time_float = $row_data[0][8];
            $logs->query_time_numeric = $row_data[0][8];
            $logs->quested_page = str_replace('"', '', $row_data[0][9]);
            if (str_replace('"', '', $row_data[0][11]) != "-") {
                $logs->user_ip = str_replace('"', '', $row_data[0][11]);
            } elseif (str_replace('"', '', $row_data[0][11]) === "-") {
                $logs->user_ip = $row_data[0][0];
            }
            $logs->uploaded_file = $uploadedfile->filename_id;
            $logs->browser_info = $useragents->user_agent_id;
            $logs->save();
        }
        return 0;
    }
}