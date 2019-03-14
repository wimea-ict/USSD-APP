<?php 
    include_once 'connectionStr.php';
    include_once 'Test.php';
    include_once 'SelectAdvisory.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <?php 
        //$test = new Test();
        //$test->getMenus();
        $counter=2;
        $test2= new SelectAdvisory();
		$phone="0774863109";
		$sess_id="987890";
        $test2->SelectAdvisory($counter);
    ?>
</body>
</html>
