<?php 
    function test_data($data){
        $data =  trim($data);
        $data = stripslashes($data);
        return $data;
    }