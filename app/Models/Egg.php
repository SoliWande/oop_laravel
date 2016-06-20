<?php
namespace App\Models;

class Egg
{

    public function action($objData, $data)
    {
        /** get coordinates */
        $oldCoordinates = $objData->coordinates;

        $idOwner = intval(@$objData->owner);

        $dataReturn = [
            'action' => config('world.action.stay'),
            'oldCoordinates' => $oldCoordinates,
            'newObj' => [
                'coordinates' => $oldCoordinates,
                'id' => $idOwner,
                'name' => app('BuildWorld')->getNameAnimal($idOwner).'_'.app('BuildWorld')->getIdNext($idOwner),
                'class' => app('BuildWorld')->getClassAnimal($idOwner),
                'css' => app('BuildWorld')->getCssAnimal($idOwner),
                'att' => app('BuildWorld')->getAttribute($idOwner)
            ]
        ];

        app('BuildWorld')->writeInfoLog("Egg {$oldCoordinates} raise to ".app('BuildWorld')->getClassAnimal($idOwner));
        return $dataReturn;
    }
}
