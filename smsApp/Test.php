
<?php 

        class Test extends connectionStr{
            public function getMenus(){
                $data;
                $sql="SELECT * FROM ussdmenu";
                $result=$this->connectionFc()->query($sql);
                $numRows=$result->num_rows;

                if($numRows > 0){
                    while($row=$result->fetch_assoc()){
                        $data[]=$row;
                    }
                }

                foreach($data as $dat){
                    echo $dat['menuname']." ".$dat['menuid']." ".$dat['parentid']." ".
                    $dat['menulevel']." ". $dat['menudescription']."<br/>";
                }
            }
        }
?>
