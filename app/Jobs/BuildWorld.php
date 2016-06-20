<?php

namespace App\Jobs;

class BuildWorld
{


    /**
     * lay thong tin cua animal theo id
     * @param $id
     * @return array
     */
    public function getAttribute($id)
    {
        $Animal = app('Animal');
        $att = array(
            'move' => $Animal->getMove($id),
            'create' => $Animal->getCreate($id),
            'spawn' => $Animal->getSpawn($id),
            'eat' => $Animal->getEat($id),
            'lay' => $Animal->getLay($id)
        );
        return $att;
    }


    /**
     * lay class cua animal theo id
     * @param $id
     * @return array
     */
    public function getIdNext($id)
    {
        switch($id){
            case config('animal.dinosaur.id'):
                $idNow = session('dino_born')+1;
                session()->put('dino_born',$idNow);
                return $idNow;
                break;
            case config('animal.falcon.id'):
                $idNow = session('falcon_born')+1;
                session()->put('falcon_born',$idNow);
                return $idNow;
                break;
            case config('animal.chicken.id'):
                $idNow = session('chicken_born')+1;
                session()->put('chicken_born',$idNow);
                return $idNow;
                break;
            default:
                $idNow = session('chicken_born')+1;
                session()->put('chicken_born',$idNow);
                return $idNow;
        }
    }
    /**
     * lay class cua animal theo id
     * @param $id
     * @return array
     */
    public function getClassAnimal($id)
    {
        switch($id){
            case config('animal.dinosaur.id'):
                return config('animal.dinosaur.class');
                break;
            case config('animal.falcon.id'):
                return config('animal.falcon.class');
                break;
            case config('animal.chicken.id'):
                return config('animal.chicken.class');
                break;
            default:
                return config('animal.chicken.class');
        }
    }

    /**
     * lay class cua animal theo id
     * @param $id
     * @return array
     */
    public function getCssAnimal($id)
    {
        switch($id){
            case config('animal.dinosaur.id'):
                return config('animal.dinosaur.css');
                break;
            case config('animal.falcon.id'):
                return config('animal.falcon.css');
                break;
            case config('animal.chicken.id'):
                return config('animal.chicken.css');
                break;
            default:
                return config('animal.chicken.css');
        }
    }



    /**
     * lay name cua animal theo id
     * @param $id
     * @return array
     */
    public function getNameAnimal($id)
    {
        switch($id){
            case config('animal.dinosaur.id'):
                return config('animal.dinosaur.name');
                break;
            case config('animal.falcon.id'):
                return config('animal.falcon.name');
                break;
            case config('animal.chicken.id'):
                return config('animal.chicken.name');
                break;
            default:
                return config('animal.chicken.name');
        }
    }



    public function getRandomInArray($array = array())
    {
        $result = null;
        if (empty($array)) {
            return $result;
        }
        $index = array_rand($array);
        $result = ['index'=> $index, 'value' =>$array[$index]];
        return $result;
    }

    public function getEmptyCell($value, $coordinatesExist = array()){
        $result = [];
        if(empty($value) || empty($coordinatesExist)){
            return null;
        }

        if(!is_array($value)){
            if(!in_array($value, $coordinatesExist)){
                $result[] = $value;
            }
        }else{
            foreach ($value as $oneValue){
                if(!in_array($oneValue, $coordinatesExist)){
                    $result[] = $oneValue;
                }
            }
        }

        return $result;
    }

    /**
     * lấy các tọa độ có thể đi của đối tượng
     * @param $step
     * @param $animalCoordinates
     * @return array
     */
    public function stepCanMove($step,$animalCoordinates){
        $x = $animalCoordinates[0];
        $y = $animalCoordinates[1];
        $result = [];

        if ($x > config('world.min')) {
            $result[] = ($x - $step).'_'.$y;
        }
        if ($x < config('world.max')) {
            $result[] = ($x + $step).'_'.$y;
        }
        if ($y > config('world.min')) {
            $result[] = $x.'_'.($y - $step);
        }
        if ($y < config('world.max')) {
            $result[] = $x.'_'.($y + $step);
        }

        return $result;
    }

    public function writeInfoLog($msg){
        // nếu có history thì lưu thêm không thì tạo mới
        if (!empty(session('log'))) {
            $history = session('log');
            $history[] = $msg;
            session()->put('log', $history);
        }else{
            $newArray[] = $msg;
            session()->put('log', $newArray);
        }
    }

}
