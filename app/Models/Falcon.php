<?php
namespace App\Models;

class Falcon{

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
            app('BuildWorld')->writeInfoLog("falcon {$oldCoordinates} starved ");
            return $dataReturn;
        }
        //unset đối tượng xử lý khỏi world trước.
        $workingData = $data;
        unset($workingData[$objData->coordinates]);

        /** kiểm tra xem bên cạnh nó có con nào ăn đc ko */
        $checkEat = $this->scanEatAI($objCoordinates, $workingData);
        if($checkEat){
            // tìm thấy rồi thì reset độ starve
            $objData->att->lay = app('Animal')->getLay(config('animal.falcon.id'));
            // update lại tọa độ
            $objData->coordinates = $checkEat['index'];
            // update số bước đi để chuẩn bị đẻ
            $objData->att->create = ($objData->att->create - 1 >0 )? $objData->att->create - 1: 0;
            $dataReturn = [
                'action' => config('world.action.eat'),
                'coordinates' => $checkEat['index'],
                'oldCoordinates' => $oldCoordinates,
                'newObj' => $objData
            ];

            $beHunted = (array)$checkEat['value']['eat'];
            $nameBeHunted = $beHunted['class'];
            $coordinatesHunted = $beHunted['coordinates'];
            app('BuildWorld')->writeInfoLog("falcon {$oldCoordinates} eat {$nameBeHunted} in coordinates {$coordinatesHunted} ");
            return $dataReturn;
        }

        /** không ăn được thì check xem có đẻ ko */
        $stepCanMove = app('BuildWorld')->stepCanMove(1,$objCoordinates);
        $createStep = intval($objData->att->create);
        $nameOwner = $objData->name;
        // check nếu số bước đi create đã tụt về 0  thì check tọa độ để đẻ
        if($createStep==0){
            $spawnArray = $objData->att->spawn;
            $checkBorn = $this->checkBorn($stepCanMove, $spawnArray, $coordinatesExist, $nameOwner);
            if($checkBorn){
                // tìm thấy rồi thì reset thời gian đẻ
                $objData->att->create = app('Animal')->getLay(config('animal.falcon.id'));
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
                app('BuildWorld')->writeInfoLog("falcon {$oldCoordinates} born Egg in coordinates {$coordinatesBorn} ");
                return $dataReturn;
            }
        }

        /** không ăn không đẻ thì đành phải lê bước */
        $checkMove = $this->scanMoveAI($objCoordinates, $workingData);
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

            app('BuildWorld')->writeInfoLog("falcon {$oldCoordinates} move to coordinates {$checkMove['value']} ");
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

        app('BuildWorld')->writeInfoLog("falcon {$oldCoordinates} stayed ");
        return $dataReturn;

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
                    'css' => 'falcon_egg',
                    'name' => config('animal.egg.name').'_'.$nameOwner.'_'.$i,
                    'owner' => intval(@$spawnArray[0])
                ];
            }

        }

        return $cellEgg;
    }

    public function findDinosaurTurnUp($coordinates, $workingData){
        if(isset($workingData[$coordinates])){
            $value = (array) $workingData[$coordinates];
            $idTermite = intval($value['id']);
            // nếu là khủng long thì quay đầu, bỏ hướng đó
            if($idTermite == config('animal.dinosaur.id')){
                return 1; // thấy khủng long
            }
            return 2; // thấy con falcon khác
        }
        return 3; // đi được
    }


    /**
     * @param $objCoordinates
     * @param $workingData
     * @return bool
     */
    public function scanMoveAI($objCoordinates, $workingData){
        $x = $objCoordinates[0];
        $y = $objCoordinates[1];
        $result = [];

        /** tìm các ô ngang có thể đi */
        // không bị giới hạn bước đi, tìm các ô chéo và ngang dọc
        // từ vị trí đang có tới min
        $move = [];
        for($i = 1; $x-$i >= config('world.min'); $i++){
            $finding = $this->findDinosaurTurnUp($x-$i.'_'.$y, $workingData);
            if($finding == 1){
                $move = [];
                break;
            }else if($finding == 2){
                break;
            }else{
                $move[] = ($x-$i).'_'.$y;
            }
        }
        $result = array_merge($result,$move);

        $move = [];
        // từ vị trí đang có tới max
        for($i = 1; $x+$i <= config('world.max'); $i++){
            $finding = $this->findDinosaurTurnUp($x+$i.'_'.$y, $workingData);
            if($finding == 1){
                $move = [];
                break;
            }else if($finding == 2){
                break;
            }else{
                $move[] = ($x+$i).'_'.$y;
            }
        }
        $result = array_merge($result,$move);

        /** tìm các ô dọc có thể đi */
        $move = [];
        // từ vị trí đang có tới min
        for($i = 1; $y-$i >= config('world.min'); $i++){
            $finding = $this->findDinosaurTurnUp($x.'_'.$y-$i, $workingData);
            if($finding == 1){
                $move = [];
                break;
            }else if($finding == 2){
                break;
            }else{
                $move[] = $x.'_'.($y-$i);
            }
        }
        $result = array_merge($result,$move);

        $move = [];
        // từ vị trí đang có tới max
        for($i = 1; $y+$i <= config('world.max'); $i++){
            $finding = $this->findDinosaurTurnUp($x.'_'.($y+$i), $workingData);
            if($finding == 1){
                $move = [];
                break;
            }else if($finding == 2){
                break;
            }else{
                $move[] = $x.'_'.($y+$i);
            }
        }
        $result = array_merge($result,$move);

        /** tìm các ô chéo có thể đi */
        $move = [];
        // -1 tới min
        for($i = 1; $x-$i >= config('world.min') && $y-$i >= config('world.min'); $i++){
            $finding = $this->findDinosaurTurnUp(($x-$i).'_'.($y-$i), $workingData);
            if($finding == 1){
                $move = [];
                break;
            }else if($finding == 2){
                break;
            }else{
                $move[] = ($x-$i).'_'.($y-$i);
            }
        }
        $result = array_merge($result,$move);

        $move = [];
        // +1 tới max
        for($i = 1; $x+$i <= config('world.max') && $y+$i <= config('world.max'); $i++){
            $finding = $this->findDinosaurTurnUp(($x+$i).'_'.($y+$i), $workingData);
            if($finding == 1){
                $move = [];
                break;
            }else if($finding == 2){
                break;
            }else{
                $move[] = ($x+$i).'_'.($y+$i);
            }
        }
        $result = array_merge($result,$move);

        $move = [];
        // +1 -1
        for($i = 1; $x+$i <= config('world.max') && $y-$i >= config('world.min'); $i++){
            $finding = $this->findDinosaurTurnUp(($x+$i).'_'.($y-$i), $workingData);
            if($finding == 1){
                $move = [];
                break;
            }else if($finding == 2){
                break;
            }else{
                $move[] = ($x+$i).'_'.($y-$i);
            }
        }
        $result = array_merge($result,$move);

        $move = [];
        // -1 +1
        for($i = 1; $x-$i >= config('world.min') && $y+$i <= config('world.max'); $i++){
            $finding = $this->findDinosaurTurnUp(($x-$i).'_'.($y+$i), $workingData);
            if($finding == 1){
                $move = [];
                break;
            }else if($finding == 2){
                break;
            }else{
                $move[] = ($x-$i).'_'.($y+$i);
            }
        }
        $result = array_merge($result,$move);

        $coordinateMove = app('BuildWorld')->getRandomInArray($result);
        return $coordinateMove;
    }



    private function getFirstDataFound($x,$y,$workingData){
        $result = [];
        if(isset($workingData[$x.'_'.$y])){
            $value = (array) $workingData[$x.'_'.$y];
            $idTermite = intval($value['id']);
            // nếu k phải khủng long hoặc falcon thì cho vào thực đơn
            if($idTermite != config('animal.dinosaur.id') && $idTermite != config('animal.falcon.id')){
                $result['eat'] = $workingData[$x.'_'.$y];
            }else{
                // còn tìm thấy con khác tức là bị chặn đường
                return 1;
            }
        }

        return $result;
    }

    /**
     * không bị giới hạn bước đi, tìm các ô chéo và ngang dọc
     * @param $objCoordinates
     * @param $workingData
     * @return array
     */
    public function scanEatAI($objCoordinates, $workingData){
        $x = $objCoordinates[0];
        $y = $objCoordinates[1];
        $dataCatch = [];

        /** tìm các ô ngang có thể đi */
        // từ vị trí đang có tới min
        for($i = 1; $x-$i >= config('world.min'); $i++){
            $getFound = $this->getFirstDataFound($x-$i, $y, $workingData);
            if($getFound){
                if($getFound==1){
                    break;
                }else{
                    $dataCatch[($x-$i).'_'.$y] = $getFound;
                    break;
                }
            }
        }

        // từ vị trí đang có tới max
        for($i = 1; $x+$i <= config('world.max'); $i++){
            $getFound = $this->getFirstDataFound($x+$i, $y, $workingData);
            if($getFound){
                if($getFound==1){
                    break;
                }else{
                    $dataCatch[($x+$i).'_'.$y] = $getFound;
                    break;
                }
            }
        }

        /** tìm các ô dọc có thể đi */
        // từ vị trí đang có tới min
        for($i = 1; $y-$i >= config('world.min'); $i++){
            $getFound = $this->getFirstDataFound($x, $y-$i, $workingData);
            if($getFound){
                if($getFound==1){
                    break;
                }else{
                    $dataCatch[$x.'_'.($y-$i)] = $getFound;
                    break;
                }
            }
        }

        // từ vị trí đang có tới max
        for($i = 1; $y+$i <= config('world.max'); $i++){
            $getFound = $this->getFirstDataFound($x, ($y+$i), $workingData);
            if($getFound){
                if($getFound==1){
                    break;
                }else{
                    $dataCatch[$x.'_'.($y+$i)] = $getFound;
                    break;
                }
            }
        }

        /** tìm các ô chéo có thể đi */
        // -1 tới min
        for($i = 1; $x-$i >= config('world.min') && $y-$i >= config('world.min'); $i++){
            $getFound = $this->getFirstDataFound(($x-$i), ($y-$i), $workingData);
            if($getFound){
                if($getFound==1){
                    break;
                }else{
                    $dataCatch[($x-$i).'_'.($y-$i)] = $getFound;
                    break;
                }
            }
        }

        // +1 tới max
        for($i = 1; $x+$i <= config('world.max') && $y+$i <= config('world.max'); $i++){
            $getFound = $this->getFirstDataFound(($x+$i), ($y+$i), $workingData);
            if($getFound){
                if($getFound==1){
                    break;
                }else{
                    $dataCatch[($x+$i).'_'.($y+$i)] = $getFound;
                    break;
                }
            }
        }


        // +1 -1
        for($i = 1; $x+$i <= config('world.max') && $y-$i >= config('world.min'); $i++){
            $getFound = $this->getFirstDataFound(($x+$i), ($y-$i), $workingData);
            if($getFound){
                if($getFound==1){
                    break;
                }else{
                    $dataCatch[($x+$i).'_'.($y-$i)] = $getFound;
                    break;
                }
            }
        }

        // -1 +1
        for($i = 1; $x-$i >= config('world.min') && $y+$i <= config('world.max'); $i++){
            $getFound = $this->getFirstDataFound(($x-$i), ($y+$i), $workingData);
            if($getFound){
                if($getFound==1){
                    break;
                }else{
                    $dataCatch[($x-$i).'_'.($y+$i)] = $getFound;
                    break;
                }
            }
        }
        if(empty($dataCatch)){
            return false;
        }

        // kiểm tra có bắt được con nào k ?
        $foodFound = app('BuildWorld')->getRandomInArray($dataCatch);
        return $foodFound;
    }

}