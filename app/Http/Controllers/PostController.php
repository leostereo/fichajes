<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Usuarios;
use App\Fichajes;
use App\Mensajes;
Use Exception;
use DB;
use Carbon\Carbon;

class PostController extends Controller
{
    private $data;	

    public function store(Request $request)
    {
	$this->data = $request->all();
	$this->data['message']['text'] = strtolower($this->data['message']['text']);
	$command = $this->data['message']['text'];


	if(($command=='hola')||($command=='chau')){
		return $this->insert_register();
	}elseif($command=='ayuda'){
		return $this->provide_help();
	}elseif(preg_match('/registros/',$command)){
                return $this->get_registers();
	}else{
		return $this->provide_help();
	}
	


    }


	private function get_users (){
		
		$usuarios = Usuarios::all()->toArray();
		$msg="Usuarios: \n";
		
			foreach($usuarios as $usuario){
				$msg .= $usuario['id'].' '.$usuario['fname'].' '.$usuario['lname']."\n";
			}
	
		return urlencode($msg);
			
	}

	private function get_registers (){

	
		$tele_id = $this->data['message']['from']['id'];
		try{
		$privilege = Usuarios::where('tele_id',$tele_id)->first()->privileg;

		}catch(Exception $e){
			return "estimado , pruebe con un \"hola\" para registrarse";
		}		
	
		if($privilege==1){
		$user_id = Usuarios::where('tele_id',$tele_id)->first()->id;
		$registers = Fichajes::where('user_id',$user_id)->get();
		$msg="Registros: \n";
		
			foreach($registers as $register){
				$msg .= $register['created_at'].' '.$register['day'].' '.$register['type'].' '.
				$register['diff']."\n";
			}

		}elseif($privilege==2){

		
		$usuarios = Usuarios::all()->toArray();
		$msg="Registros: \n";
			foreach($usuarios as $usuario){
				$msg .= $usuario['id'].' '.$usuario['fname'].' '.$usuario['lname']."\n";
					$registers = Fichajes::where('user_id',$usuario['id'])
						->where('created_at', '>=', Carbon::now()->subDays(60)
						->toDateTimeString())->get();
		
						foreach($registers as $register){
							$msg .= $register['created_at'].' '.$register['day'].
							' '.$register['type'].' '.
							$register['diff']."\n";
						}

				$msg .= "--------------------------------------------------\n";
			}
			
		}

		return urlencode($msg);
			
	}

	private function insert_register (){
		$tele_id = $this->data['message']['from']['id'];
		$command = $this->data['message']['text'];
		$name = $this->data['message']['from']['first_name'];

		try{	
		$user_id = Usuarios::where('tele_id',$tele_id)->first()->id;
		}catch(Exception $e){

			if(!Usuarios::where('tele_id',$tele_id)->exists()){
				return $this->create_user();		
			}else{
				return "error 1001, insertar registro";
			}
			
		}
	
		$type = ($command == 'chau')? 'salida' : 'entrada';
		$register = new Fichajes;
		$register->user_id = $user_id;
		$register->type = $type;
		#$register->diff = '00:00:00';
		
			$day_arr = array(
				1 => 'lunes',
				2 => 'martes',
				3 => 'miercoles',
				4 => 'jueves',
				5 => 'viernes',
				6 => 'sabado',
				7 => 'domingo',
			);

		$register->day = $day_arr[date('N')];
		
		if($type=='salida'){
		
			$register->type = $type.'   ';
			$query = "select TIMEDIFF(now(),(select created_at from fichajes where user_id = $user_id".
				" and type = 'entrada' order by created_at desc limit 1)) diff";
			$results = DB::select($query, [1]);
			$register->diff = $results[0]->diff;
		}

			try{
				$register->save();
				
			}catch(Exception $e){
				return  'error 1003, Tuvimos problemas para ingresar tu ficha';
			}
		$time = date("H:i:s");	
		$msg = "$name ,";
		$msg .= ($type == 'salida')? 'nos vemos que tengas buen dia son las '.$time : 
			'bienvenido al trabajo son las '.$time;
		return $msg;

	
	}

	private function user_exist ($tele_id){
		$user = new Usuarios;
		return Usuarios::where('tele_id',$tele_id)->exists();	
	}

	private function provide_help (){
		$name=$this->data['message']['from']['first_name'];
		$tele_id=$this->data['message']['from']['id'];
		
		try{
		$privilege = Usuarios::where('tele_id',$tele_id)->first()->privileg;
		}catch(Exception $e){
			return "estimado , pruebe con un \"hola\" para registrarse";
		}		

		$privileges_map = array(
			1 => array(
				"hola" => "para marcar la hora de entrada",
				"chau" => "para marcar la hora de salida",
				"registros" => "para ver tus registros",
			),
			2 => array(
				"hola" => "para marcar la hora de entrada",
				"chau" => "para marcar la hora de salida",
				"registros" => "para ver todos los registros",
			),
			
		);		

		$help_msg = "$name , tu nivel de previlegios es $privilege\n";
		$help_msg .= "Puedes utilizar los siguientes comandos:\n";
		foreach ($privileges_map[$privilege] as $cmd => $desc){
			$help_msg .= "$cmd : $desc\n";
		}
	
		return urlencode($help_msg);
	}

	public function create_user(){


		   $user = new Usuarios;
		   $user->tele_id = $this->data['message']['from']['id'];
		   $user->fname = $this->data['message']['from']['first_name'];

			try{
		   		$user->lname = $this->data['message']['from']['last_name'];

			}catch(Exception $e){

				return "Por favor , agreaga tu apellido a la cuenta de telegram, gracias";
			}

		   $user->chat_id = $this->data['message']['chat']['id'];
		   #$user->privileg = 1;
		   #$user->category = "tecnico";
		   #$user->score = 0;
		   #$user->diff = 0;

				try
				{
					$user->save();
				}
				catch(Exception $e)
				{
				   return "1001, $user->fname hay un problema con el registro de tu usuario:";
				}

				return "Bienvenido $user->fname, ingresa \"ayuda\" para ver las opciones";

		
	}
	

    //
}
