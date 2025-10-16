<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class StoreProcedureService
{
    protected $sql;
    protected $data;
    protected $sp_name;
    protected $in_parameters;
    protected $out_parameters;

    public function name($sp_name){
        $this->sp_name = $sp_name;
        return $this;
    }

    public function InParameters($in_parameters){
        $this->in_parameters = $this->modifiedKeys(':', $in_parameters);
        return $this;
    }

    public function OutParameters($out_parameters){
        $this->out_parameters = $this->modifiedKeys('@', $out_parameters);
        return $this;
    }

    public function data($data) {
        $this->data = $data;
        return $this;
    }

    public function execute(){
        try{
            $this->sql = "CALL {$this->sp_name}(";
            
            if($this->in_parameters != null){
                $this->sql .= $this->in_parameters;
            }

            if($this->out_parameters != null){
                $this->sql .= $this->in_parameters != null ? ',' : '';
                $this->sql .= $this->out_parameters;
            }
            $this->sql .= ")";

            $fr = DB::statement($this->sql, $this->data);
            return $this;
        } catch(\Exception $ex){
            return ['response'=>$ex->getMessage()];
        }
    }

    public function response(){
        try{
            $results = DB::select("SELECT " . $this->out_parameters);
            $response = $this->getResponse($results);
            
            return ['response'=>$response];
        } catch(\Exception $ex){
            return ['response'=>$ex->getMessage()];
        }
    }

    public function getResponse($data){
        $response = ['return_value'=>0, 'return_message'=>'Something went wrong.'];
        if(count($data) > 0 && !empty($data[0]) && isset($data[0]->{'@return_value'})){
            $outArr = explode(',', $this->out_parameters);
            foreach($outArr as $outKey => $outValue){
                if(isset($data[0]->{$outValue})){
                    $response[str_replace('@', '', $outValue)] = $data[0]->{$outValue};
                }
            }
        }
        return $response;
    }

    public function modifiedKeys($split, $array){
        $keys = array_map(function($key) use($split) {
            return $split . $key . ',';
        }, array_values($array));

        return rtrim(implode('', $keys), ',');
    }
}
