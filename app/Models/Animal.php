<?php
namespace App\Models;
class Animal
{
    public function getMove($id) {
        switch($id){
            case config('animal.dinosaur.id'):
                return 1;
                break;
            case config('animal.falcon.id'):
                return 0;
                break;
            case config('animal.chicken.id'):
                return 1;
                break;
            default:
                return 1;
        }
    }

    public function getCreate($id){
        switch($id){
            case config('animal.dinosaur.id'):
                return 5;
                break;
            case config('animal.falcon.id'):
                return 3;
                break;
            case config('animal.chicken.id'):
                return 1;
                break;
            default:
                return 5;
        }
    }

    public function getSpawn($id){
        switch($id){
            case config('animal.dinosaur.id'):
                return [$id,1];
                break;
            case config('animal.falcon.id'):
                return [$id,1];
                break;
            case config('animal.chicken.id'):
                return [$id,2];
                break;
            default:
                return [$id,1];
        }
    }

    public function getEat($id){
        switch($id){
            case config('animal.dinosaur.id'):
                return [config('animal.falcon.id'), config('animal.chicken.id'), config('animal.egg.id')];
                break;
            case config('animal.falcon.id'):
                return [config('animal.chicken.id'), config('animal.egg.id')];
                break;
            case config('animal.chicken.id'):
                return [config('animal.egg.id')];
                break;
            default:
                return [config('animal.egg.id')];
        }

    }

    public function getLay($id){
        switch($id){
            case config('animal.dinosaur.id'):
                return 6;
                break;
            case config('animal.falcon.id'):
                return 4;
                break;
            case config('animal.chicken.id'):
                return 3;
                break;
            default:
                return 3;
        }
    }
}
