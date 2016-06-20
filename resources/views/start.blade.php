<!DOCTYPE html>
<html>
<head>
    <title>Laravel</title>

    <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

    <style>
        html, body {
            height: 100%;
        }

        body {
            margin: 0;
            padding: 0;
            width: 100%;
            display: table;
            font-weight: 100;
            font-family: 'Lato';
        }

        .container {
            text-align: center;
            display: table-cell;
            vertical-align: middle;
        }

        .content {
            text-align: center;
            display: inline-block;
        }

        .title {
            font-size: 96px;
        }
        table td{
            min-width: 20px;
        }

        .Dinosaur{
            background : url(/image/dinosaur.gif);
            background-size: 20px 20px;
        }
        .Chicken{
            background : url(/image/chicken.gif);
            background-size: 20px 20px;
        }
        .Falcon{
            background : url(/image/falcon.gif);
            background-size: 20px 20px;
        }
        .chicken_egg{
            background : url(/image/chicken_egg.gif);
            background-size: 20px 20px;
        }
        .dino_egg{
            background : url(/image/dino_egg.png);
            background-size: 20px 20px;
        }
        .falcon_egg{
            background : url(/image/falcon_egg.jpeg);
            background-size: 20px 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="">
        <div style="float:left;width:30%;color: black">
            <?php $log = session('log'); ?>
                @if(!empty($log))
                    @foreach($log as $oneLog)
                        <?= $oneLog.'<br/>'; ?>
                    @endforeach
                @endif

        </div>
        <div style="float:left;width:30%;color: black">
            @if(empty($worldEnd))
                <form action="nextDay" method="POST">
                    <input type="hidden" name="data" value='<?= json_encode($hasExist) ; ?>' />
                    <button type="submit">Next days</button>
                </form>
            @else
                <b>Sorry, it's time to end this world !!!</b>
            @endif


            <table border="1" cellpadding="5">
                <thead>
                <th></th>
                <!-- vẽ hàng ngang -->
                <?php $arr=[];for($i = config('world.min'); $i <= config('world.max'); $i++){
                    $arr[$i] = $i;
                    echo '<th>'.$i.'</th>';
                }
                ?>
                </thead>
                <tbody>
                <!-- vẽ hàng dọc -->
                <?php for($i = config('world.min'); $i <= config('world.max'); $i++){ ?>
                <tr>
                    <td><?= $i; ?></td>
                    <?php for($j = config('world.min'); $j <= config('world.max'); $j++){
                    $class = '';
                    if(!empty($hasExist[$arr[$j].'_'.$i])){
                        $data = (array) $hasExist[$arr[$j].'_'.$i];
                        $class.= $data['css'].' hasAnimal';
                    }
                    ?>
                    <td><div id="<?= $arr[$j].'_'.$i; ?>" class="<?= $class; ?>">&nbsp;</div></td>
                    <?php } ?>
                </tr>
                <?php
                }
                ?>
                </tbody>
            </table>

        </div>
        <div style="float:right;width:40%;">
            @if(!empty($history))
            <?php $day = count($history); ?>
                @foreach($history as $oneHistory)
                <div style="float:left; margin-right: 5px;">
                    DAY <?= $day; ?>
                <table border="1" cellpadding="5">
                    <thead>
                        <th></th>
                        <!-- vẽ hàng ngang -->
                        <?php $arr=[];for($i = config('world.min'); $i <= config('world.max'); $i++){
                            $arr[$i] = $i;
                            echo '<th>'.$i.'</th>';
                        }
                        ?>
                    </thead>
                    <tbody>
                        <!-- vẽ hàng dọc -->
                        <?php for($i = config('world.min'); $i <= config('world.max'); $i++){ ?>
                        <tr>
                            <td><?= $i; ?></td>
                            <?php for($j = config('world.min'); $j <= config('world.max'); $j++){
                            $class = '';
                            if(!empty($oneHistory[$arr[$j].'_'.$i])){
                                $data = (array) $oneHistory[$arr[$j].'_'.$i];
                                $class.= $data['css'].' hasAnimal';
                            }
                            ?>
                            <td><div id="<?= $arr[$j].'_'.$i; ?>" class="<?= $class; ?>">&nbsp;</div></td>
                            <?php } ?>
                        </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
                </div>
                    <?php $day--; ?>
                @endforeach
            @endif
        </div>
    </div>
</div>
</body>
</html>
