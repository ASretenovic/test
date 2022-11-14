<?php

class Manage{


    private $con;

    function  __construct(){
        include_once("../database/db.php");
        $db = new Database();
        $this->con =  $db->connect();
    }


    // Ucitavanje svih redova iz tabele. Naziv tabele se prosledjuje kao parametar.
    public function manageRecord($table){
        if($table == "kategorija"){
            $sql = "SELECT * FROM kategorija";
        } 
        
        else if($table == "proizvod"){
            $sql = "SELECT * FROM proizvod";
        } 
        $result = $this->con->query($sql) or die($this->con->error);
        $rows = array();
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $rows[] = $row;   
            }
        }
        return $rows;
    }


     // Ucitavanje elementa na osnovu primarnog kljuca. Naziv tabele i naziv kljuca se prosledjuju kao parametri.
     public function getSingleRecord($table,$id){
        if($table == "kategorija"){

            $prepared_statement = $this->con->prepare("SELECT * FROM kategorija WHERE kid = ?");
            $prepared_statement->bind_param("i",$id);
            $prepared_statement->execute() or die($this->con->error);
            $result = $prepared_statement->get_result();

            if($result->num_rows == 1){
                $row = $result->fetch_assoc();
            }
            return $row;

        } else if($table == "proizvod"){
            $prepared_statement = $this->con->prepare("SELECT * FROM proizvod WHERE pid = ?");
            $prepared_statement->bind_param("i",$id);
            $prepared_statement->execute() or die($this->con->error);
            $result = $prepared_statement->get_result();

            if($result->num_rows == 1){
                $row = $result->fetch_assoc();
            }
            return $row;

        }
    }


 
    // Azuriranje vise polje u tabeli. Naziv tabele, niz polja za izmenu i niz novih vrednosti su parametri.
    public function update_record($table,$where,$fields){
        $sql="";
        $condition="";
        foreach($where as $key=>$value){
            $condition .= $key . "='" . $value."' , ";
        }

        $condition = substr($condition,0,-3);

        foreach($fields as $key=>$value){
            $sql .= $key . "='" . $value ."' , ";
        }

        $sql = substr($sql,0,-3);
        $sql = "UPDATE ".$table." SET ".$sql." WHERE ".$condition;
        if(mysqli_query($this->con,$sql)) {
            return "UPDATED";
        }
        return "UPDATE_FAILED";
    }





    // Brisanje reda iz tabele na osnovu kljuca. Naziv tabele i primarni kljuc se prosledjuju kao parametri.
    public function deleteRecord($table,$id){
        // brisanje kategorije
        if($table == "kategorija"){
            $prep_stat = $this->con->prepare("SELECT * FROM kategorija k LEFT JOIN proizvod p ON k.kid = p.kid 
                                                WHERE p.kid = ?");
            $prep_stat->bind_param("i",$id);
            $prep_stat->execute()  or die($this->con->error);
            $res_check = $prep_stat->get_result();

            if($res_check->num_rows > 0){
                return "DELETE_RESTRICTED";
            } else{
            $prepared_statement = $this->con->prepare("SELECT kid FROM kategorija WHERE kid = ?");
            $prepared_statement->bind_param("i",$id);
            $prepared_statement->execute()  or die($this->con->error);
            $result = $prepared_statement->get_result();

            if($result->num_rows == 1){
                $prepared_statement = $this->con->prepare("DELETE FROM kategorija WHERE kid = ?");
                $prepared_statement->bind_param("i",$id);
                $result = $prepared_statement->execute() or die($this->con->error);;
                if($result){
                    return "CATEGORY_DELETED";
                } else{
                    return "ERROR_DELETE_CATEGORY";
                }
            } 
        }

        // brisanje proizvoda
        } else if($table == "proizvod"){
            $prepared_statement = $this->con->prepare("DELETE FROM proizvod WHERE pid = ?");
            $prepared_statement->bind_param("i",$id);
            $result = $prepared_statement->execute() or die($this->con->error);
            if($result){
                return "PRODUCT_DELETED";
            } else{
                return "ERROR_DELETE_PRODUCT";
            }
        }
    }



}