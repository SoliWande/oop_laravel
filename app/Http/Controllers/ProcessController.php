<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class ProcessController extends Controller
{
    public $buildWorld;
    public $data = array();
    public $coordinatesRandom = array();
    public function __construct()
    {
        $this->buildWorld = app('BuildWorld');
    }

    public function start(Request $request){
        // kill all session
        $request->session()->flush();
        $this->buildWorld->writeInfoLog("======== Start World =========");
        $hasExist = [];
        /** random 1 Dinosaur */
        $dinoCoordinates = $this->generatorRandomCoordinates();
        $dino = array(
            'coordinates' => $dinoCoordinates,
            'id' => config('animal.dinosaur.id'),
            'name' => config('animal.dinosaur.name').'_1',
            'class' => config('animal.dinosaur.class'),
            'css' => config('animal.dinosaur.css'),
            'att' => $this->buildWorld->getAttribute(config('animal.dinosaur.id'))
        );
        // lưu lại số lượng dino đã sinh
        $request->session()->put('dino_born', 1);
        // lưu lại tọa độ đã có thú
        $hasExist[$dinoCoordinates] = $dino;

        /** random 2 Falcon */
        for($i = 1; $i <= 2; $i++){
            $falCoordinates = $this->generatorRandomCoordinates();
            $fal = array(
                'coordinates' => $falCoordinates,
                'id' => config('animal.falcon.id'),
                'name' => config('animal.falcon.name').'_'.$i,
                'class' => config('animal.falcon.class'),
                'css' => config('animal.falcon.css'),
                'att' => $this->buildWorld->getAttribute(config('animal.falcon.id'))
            );
            $hasExist[$falCoordinates] = $fal;
        }
        // lưu lại số lượng falcon đã sinh
        $request->session()->put('falcon_born', 2);

        /** random 4 Chicken */
        for($i = 1; $i <= 8; $i++){
            $chickCoordinates = $this->generatorRandomCoordinates();
            $chick = array(
                'coordinates' => $chickCoordinates,
                'id' => config('animal.chicken.id'),
                'name' => config('animal.chicken.name').'_'.$i,
                'class' => config('animal.chicken.class'),
                'css' => config('animal.chicken.css'),
                'att' => $this->buildWorld->getAttribute(config('animal.chicken.id'))
            );
            $hasExist[$chickCoordinates] = $chick;
        }
        // lưu lại số lượng chicken đã sinh
        $request->session()->put('chicken_born', 4);

        return view('start', [
            'hasExist' => $hasExist
        ]);
    }

    // xử lý mỗi lần next đây
    public function nextDay(Request $request){
        // lay du lieu
        $data = $request->input('data');
        $data = (array) json_decode($data);

        // set data vao pubic variable
        $this->data = $data;

        // sap xep du lieu de xu ly
        $dataToSort = $data;
        usort($dataToSort,array( $this, 'cmp' ));
        //var_dump($this->data);die();

        // xu ly du lieu
        foreach ($dataToSort as $key =>$oneData){
            if(isset($this->data[$oneData->coordinates])){
                $valueCheck = (array) $this->data[$oneData->coordinates];
                if($valueCheck['name'] === $oneData->name){
                    $dataReturn = app($oneData->class)->action($oneData, $this->data);
                    if(!empty($dataReturn)){
                        $this->processAction($dataReturn);
                    }
                }
            }
        }

        // check xem con nào sống sót cuối cùng
        $checkData = (array)$this->data;
        $surviveType = [];
        foreach ($checkData as $oneData){
            $oneData = (array)$oneData;
            // lay class
            $class = $oneData['class'];
            // neu la egg thi lay id owner
            if($class == config('animal.egg.class')){
                $class = $oneData['owner'];
            }
            // chua co thi them vao
            if(!isset($surviveType[$class])){
                $surviveType[$class] = $class;
            }
        }

        $worldEnd = false;
        if(count($surviveType) <= 1){
            $worldEnd = true;
        }

        return view('start', [
            'history' => $request->session()->get('history'),
            'hasExist' => $this->data,
            'worldEnd' => $worldEnd
        ]);
    }


    private function cmp($a,$b){
        if(  $a->coordinates ==  $b->coordinates ){
            return 0 ;
        }
        $coordinates_a = explode('_',$a->coordinates);
        $coordinates_b = explode('_',$b->coordinates);

        if($coordinates_a[0] < $coordinates_b[0]){
            return -1;
        }else if ($coordinates_a[0] > $coordinates_b[0]){
            return 1;
        }else{
            if($coordinates_a[1] < $coordinates_b[1]){
                return -1;
            }else{
                return 1;
            }
        }

    }

    /**
     * @param $dataReturn
     * @return mixed
     */
    public function processAction($dataReturn){
        $action = intval(@$dataReturn['action']);
        switch($action){
            case config('world.action.eat'):
                /** ăn 1 con nào đó */
                // xóa vị trí cũ
                unset($this->data[$dataReturn['oldCoordinates']]);
                unset($this->data[$dataReturn['coordinates']]);
                // cập nhật đè vào vị trị mới
                $this->data[$dataReturn['coordinates']] = $dataReturn['newObj'];
                break;
            case config('world.action.born'):
                /** update lai data */
                $this->data[$dataReturn['oldCoordinates']] = $dataReturn['newObj'];
                // đẻ trứng vào world
                foreach($dataReturn['egg'] as $key=>$oneEgg){
                    $this->data[$key] = $oneEgg;
                }
                break;
            case config('world.action.move'):
                // xóa vị trí cũ
                unset($this->data[$dataReturn['oldCoordinates']]);
                unset($this->data[$dataReturn['coordinates']]);
                // cập nhật đè vào vị trị mới
                $this->data[$dataReturn['coordinates']] = $dataReturn['newObj'];
                break;
            case config('world.action.die'):
                /** chết thì xóa khỏi world */
                unset($this->data[$dataReturn['oldCoordinates']]);
                break;
            case config('world.action.stay'):
                /** update lai data */
                unset($this->data[$dataReturn['oldCoordinates']]);
                $this->data[$dataReturn['oldCoordinates']] = $dataReturn['newObj'];
                break;
        }
    }

    /**
     * random vị trí ban đầu
     * @return string
     */
    public function generatorRandomCoordinates(){
        $ranX = rand(config('world.min'),config('world.max'));
        $ranY = rand(config('world.min'),config('world.max'));
        if(!empty($this->coordinatesRandom)){
            if(array_key_exists($ranX.'_'.$ranY,$this->coordinatesRandom)){
                return $this->generatorRandomCoordinates();
            }
        }
        $this->coordinatesRandom[$ranX.'_'.$ranY] = $ranX.'_'.$ranY;
        return $ranX.'_'.$ranY;
    }
}