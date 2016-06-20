<?php
namespace App\Models;

class Chicken{

    public function action($objData, $data){
        /** get coordinates */
        $oldCoordinates = $objData->coordinates;
        $objCoordinates = explode('_',$objData->coordinates);
        $coordinatesExist = array_keys($data);

        // neu da den thoi gian nam xuong thi remove khoi world
        if($objData->att->lay == 0){
            $dataReturn = [
                'action' => config('world.action.die'),
                'oldCoordinates' => $oldCoordinates,
            ];
            app('BuildWorld')->writeInfoLog("chicken {$oldCoordinates} starved ");
            return $dataReturn;
        }

        /** kiểm tra xem có thể đi những bước nào */
        $step = $objData->att->move;
        $stepCanMove = app('BuildWorld')->stepCanMove($step,$objCoordinates);

        //unset đối tượng xử lý khỏi world trước.
        $workingData = $data;
        unset($workingData[$objData->coordinates]);

        /** kiểm tra xem bên cạnh nó có con nào ăn đc ko */
        $foodArray = $objData->att->eat;
        $checkEat = $this->checkEat($stepCanMove,$foodArray,$workingData);
        if($checkEat){
            // tìm thấy rồi thì reset độ starve
            $objData->att->lay = app('Animal')->getLay(config('animal.chicken.id'));
            // update lại tọa độ
            $objData->coordinates = $checkEat['newCoordinates'];
            // update số bước đi để chuẩn bị đẻ
            $objData->att->create = ($objData->att->create - 1 >0 )? $objData->att->create - 1: 0;
            $dataReturn = [
                'action' => config('world.action.eat'),
                'coordinates' => $checkEat['newCoordinates'],
                'oldCoordinates' => $oldCoordinates,
                'newObj' => $objData
            ];

            $beHunted = (array)$checkEat['eat'];
            $nameBeHunted = $beHunted['class'];
            $coordinatesHunted = $beHunted['coordinates'];
            app('BuildWorld')->writeInfoLog("Chicken {$oldCoordinates} eat {$nameBeHunted} in coordinates {$coordinatesHunted} ");
            return $dataReturn;
        }

        /** không ăn được thì check xem có đẻ ko */
        $createStep = intval($objData->att->create);
        $nameOwner = $objData->name;
        // check nếu số bước đi create đã tụt về 0  thì check tọa độ để đẻ
        if($createStep==0){
            $spawnArray = $objData->att->spawn;
            $checkBorn = $this->checkBorn($stepCanMove, $spawnArray, $coordinatesExist, $nameOwner);
            if($checkBorn){
                // tìm thấy rồi thì reset thời gian đẻ
                $objData->att->create = app('Animal')->getLay(config('animal.chicken.id'));
                // tọa độ đứng im
                // update số bước đi để chuẩn bị chết
                $objData->att->lay = ($objData->att->lay - 1 >0 )? $objData->att->lay - 1: 0;
                $dataReturn = [
                    'action' => config('world.action.born'),
                    'egg' => $checkBorn,
                    'oldCoordinates' => $oldCoordinates,
                    'newObj' => $objData
                ];

                $coordinatesBorn = '';
                foreach ($checkBorn as $oneBorn){
                    $coordinatesBorn.= $oneBorn['coordinates'].',';
                }
                $coordinatesBorn = ltrim($coordinatesBorn);
                app('BuildWorld')->writeInfoLog("Chicken {$oldCoordinates} born Egg in coordinates {$coordinatesBorn} ");
                return $dataReturn;
            }
        }

        /** không ăn không đẻ thì đành phải lê bước */
        $checkMove = $this->checkMove($stepCanMove,$coordinatesExist);

        if(!empty($checkMove)){
            // update lại tọa độ
            $objData->coordinates = $checkMove['value'];
            // update số bước đi để chuẩn bị đẻ
            $objData->att->create = ($objData->att->create - 1 >0 )? $objData->att->create - 1: 0;
            // update số bước đi để chuẩn bị chết
            $objData->att->lay = ($objData->att->lay - 1 >0 )? $objData->att->lay - 1: 0;
            $dataReturn = [
                'action' => config('world.action.move'),
                'coordinates' => $checkMove['value'],
                'oldCoordinates' => $oldCoordinates,
                'newObj' => $objData
            ];

            app('BuildWorld')->writeInfoLog("Chicken {$oldCoordinates} move to coordinates {$checkMove['value']} ");
            return $dataReturn;
        }

        /** không đi được nữa thì đứng im */
        // update số bước đi để chuẩn bị đẻ
        $objData->att->create = ($objData->att->create - 1 > 0) ? $objData->att->create - 1 : 0;
        // update số bước đi để chuẩn bị chết
        $objData->att->lay = ($objData->att->lay - 1 > 0) ? $objData->att->lay - 1 : 0;
        $dataReturn = [
            'action' => config('world.action.stay'),
            'oldCoordinates' => $oldCoordinates,
            'newObj' => $objData
        ];

        app('BuildWorld')->writeInfoLog("Chicken {$oldCoordinates} stayed ");
        return $dataReturn;

    }

    /**
     * check xem có ăn được không
     * @param $stepCanMove
     * @param $foodArray
     * @param $workingData
     * @return array|bool
     */
    public function checkEat($stepCanMove,$foodArray,$workingData){
        if(empty($stepCanMove)){
            return false;
        }

        $dataCatch = false;
        // kiểm tra có bắt được con nào k ?
        foreach($workingData as $key=>$value){
            // nếu bắt được
            if(in_array($key,$stepCanMove)){
                $value = (array) $value;
                $idTermite = intval($value['id']);
                // kiểm tra có ăn được k ? cùng loài thì lại tìm con khác
                if(in_array($idTermite,$foodArray)){
                    $dataCatch = [];
                    $dataCatch['eat'] = $workingData[$key];
                    $dataCatch['newCoordinates'] = $key;
                    break;
                }
            }
        }

        return $dataCatch;

    }

    /**
     * check xem có đẻ được không.
     * Nếu có trả về mảng tọa độ các vị trí của trứng
     * @param $stepCanMove
     * @param $spawnArray
     * @param $coordinatesExist
     * @param $nameOwner
     * @return array|bool
     */
    public function checkBorn($stepCanMove, $spawnArray, $coordinatesExist, $nameOwner){
        if(empty($stepCanMove)){
            return false;
        }

        $emptyCellCanBorn = [];
        // kiểm tra xung quanh còn ô nào đẻ đc k
        foreach($stepCanMove as $oneStep){
            if(!in_array($oneStep,$coordinatesExist)){
                $emptyCellCanBorn[] = $oneStep;
            }
        }

        // lấy số lượng trứng cần đẻ để lấy tọa độ
        $qtyEgg = $spawnArray[1];
        $cellEgg = [];
        for($i= 1; $i <= $qtyEgg; $i++){
            $coordinateEgg = app('BuildWorld')->getRandomInArray($emptyCellCanBorn);
            if(!empty($coordinateEgg)){
                // xoa vi tri
                unset($emptyCellCanBorn[$coordinateEgg['index']]);
                // luu data
                $cellEgg[$coordinateEgg['value']] = [
                    'coordinates' => $coordinateEgg['value'],
                    'id' => config('animal.egg.id'),
                    'class' => config('animal.egg.class'),
                    'css' => 'chicken_egg',
                    'name' => config('animal.egg.name').'_'.$nameOwner.'_'.$i,
                    'owner' => intval(@$spawnArray[0])
                ];
            }

        }

        return $cellEgg;
    }

    /**
     * @param $stepCanMove
     * @param $coordinatesExist
     * @return bool
     */
    public function checkMove($stepCanMove, $coordinatesExist){
        if(empty($stepCanMove)){
            return false;
        }

        $emptyCellCanMove = [];
        // kiểm tra xung quanh còn ô nào trống ko
        foreach($stepCanMove as $oneStep){
            if(!in_array($oneStep,$coordinatesExist)){
                $emptyCellCanMove[] = $oneStep;
            }
        }

        $coordinateMove = app('BuildWorld')->getRandomInArray($emptyCellCanMove);
        return $coordinateMove;
    }
}