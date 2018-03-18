<?php
/**
 * Created by PhpStorm.
 * User: Nariman
 * Date: 17/03/2018
 * Time: 10:17 AM
 */

class FaceApp
{
    private $default_api_host = 'https://node-01.faceapp.io/api/v2.8/photos';
    private $default_user_agent = 'FaceApp/1.0.229 (Linux; Android 4.4)';
    private $device_id_length = 8;
    private $devide_id_letters = '';
    private $header_options = [];
    private $PhotoOptions = NULL;
    private $Photo = NULL;
    public function __construct(string $photo_path)
    {
        $this->devide_id_letters = substr(join('', range('a', 'z')),
            rand(0, 25 - $this->device_id_length),
            $this->device_id_length);
        $this->header_options = [
            'User-Agent:' . $this->default_user_agent,
            'X-FaceApp-DeviceID:' . $this->devide_id_letters
        ];
        $this->PhotoOptions = $this->UploadPhoto($photo_path);
    }
    public function getPhotoCode()
    {
        return (!empty($this->PhotoOptions->code)) ? $this->PhotoOptions->code : null;
    }
    public function getFilters()
    {
        if(!empty($this->PhotoOptions->filters)) {
            return array_map(function ($array)
            {
                return $array[0];
            }, $this->PhotoOptions->filters);
        }else{
            return null;
        }
    }
    public function savePhoto(string $photo_path)
    {
        if (!empty($this->Photo)) {
            file_put_contents($photo_path, $this->Photo);
            return true;
        }
        else {
            exit("No Photo Has Been Created!\nFirst Run Apply_Filter() On Photo.");
        }
    }
    private function uploadPhoto($photo_path)
    {
        try {
            if (!file_exists($photo_path)) throw new \Exception("Photo Not Find!");
            if (!getimagesize($photo_path)) throw new \Exception("Input File Is NOT Photo!");
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header_options);
            curl_setopt($ch, CURLOPT_URL, $this->default_api_host);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, ['file' => new \CURLFile($photo_path)]);
            $response = json_decode(curl_exec($ch));
            print_r($response, true);
            if (isset($response->err))
                throw new \Exception($response->err->desc);
            else {
                function getFilter_id($array)
                {
                    $croped = (isset($array->only_cropped) && $array->only_cropped == 1) ? 1 : 0;
                    return [$array->id, $croped];
                }

                $code = $response->code;
                $filters = array_map("getFilter_id", $response->filters);
                return (object)['code' => $code, 'filters' => $filters];
            }
        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    public function applyFilter(string $photoCode,string $filter, bool $crop = false)
    {
        try {

            $filters = array_map(function ($array)
            {
                return $array[0];
            }, $this->PhotoOptions->filters);
            if (!in_array($filter, $filters)) throw new Exception('Filter Not Found!');
            $arraykey = array_search($filter, $filters);

            if ($crop == false && $this->PhotoOptions->filters[$arraykey][1] == true)
                $crop = true;
            $crop = ($crop == true) ? 'true' : 'false';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header_options);
            curl_setopt($ch, CURLOPT_URL, $this->default_api_host . "/" . $photoCode . "/filters/" . $filter . "?cropped=".$crop);
            $res = curl_exec($ch);
            if (curl_getinfo($ch)["http_code"] == 200) {
                $this->Photo = $res;
            } else {
                switch (curl_getinfo($ch)["http_code"]) {
                    case 400 :
                        throw new Exception('Photo Must be Cropped');
                        break;
                    case 404 :
                        throw new Exception('Filter Not Found!');
                        break;
                    default :
                        throw new Exception('Unknown Error...!!');
                }
            }
        } catch (Exception $e) {
            exit($e->getMessage());
        }

    }
}