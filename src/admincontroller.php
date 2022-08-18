<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use stdClass;
use DateTime;
class SelectoresController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
       // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('empresas');
    }

    public function distancia(){


    $addressTo=$_REQUEST['inicio'];
    $addressFrom=$_REQUEST['final'];

    //fin de consulta
    date_default_timezone_set('Etc/UTC');
    //Change address format
    $formattedAddrFrom = str_replace(' ','+',$addressFrom);
    $formattedAddrTo = str_replace(' ','+',$addressTo);
    
    //Send request and receive json data
    $geocodeFrom = file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.$formattedAddrFrom.'&sensor=false&key=AIzaSyBx61xi2oAGPxP80iHiJkhMM5YdLUhnOrQ');
    $outputFrom = json_decode($geocodeFrom);
    $geocodeTo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.$formattedAddrTo.'&sensor=false&key=AIzaSyBx61xi2oAGPxP80iHiJkhMM5YdLUhnOrQ');
    $outputTo = json_decode($geocodeTo);

    print_r($geocodeTo);
    
    //Get latitude and longitude from geo data
    $latitudeFrom = $outputFrom->results[0]->geometry->location->lat;
    $longitudeFrom = $outputFrom->results[0]->geometry->location->lng;
    $latitudeTo = $outputTo->results[0]->geometry->location->lat;
    $longitudeTo = $outputTo->results[0]->geometry->location->lng;
    
    //Calculate distance from latitude and longitude
    $theta = $longitudeFrom - $longitudeTo;

    $dist = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) +  cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));


    $dist = acos($dist);
    $dist = rad2deg($dist);
   
    $miles = $dist * 60 * 1.853;

   $km=($miles * 1.6093444);

   $response= new stdClass();

   $response->distancia=$km;

   echo json_encode($response);

   


    }

    public function generaprecotizacion(){


        foreach($_POST as $nombre_campo => $valor)
            { 
               $asignacion = "\$" . $nombre_campo . "='" . $valor . "';"; 
                eval($asignacion); 
                
            }

           

            $fecha1= new DateTime($fsalida);
            $fecha2= new DateTime($fregreso);

            $diff = $fecha1->diff($fecha2);
            $dias=@$diff->days. "días de viaje"; //obtiene días




            exit();


        //insertamos el cliente

         DB::table('clientes')->insert(
                  [
                  'nombres' =>$cliente,
                  'apellidos' =>$apellidos,
                  "activo"=>1,
                  "telefono"=>$telefono,
                  'domicilio'=>$domicilio,
                  "rfc"=>$rfc
                  ]);

         //consultar el ultimo id insertado para obtener el id de cliente
          $clienteID=DB::select("SELECT max(id_cliente) as id FROM clientes");


          $idCliente=$clienteID[0]->id;

          $fechaRegistro=date("Y-m-d H:i:s");

        
       
          DB::table('ordenes')->insert(
            [
            "id_cliente"=>$idCliente,
            "id_unidad_negocio"=>1,
            "fecha_salida"=>$fsalida,
            "hora_salida"=>$hsalida,
            "fecha_regreso"=>$fregreso,
            "hora_regreso"=>$hregreso,
            "distancia"=>$distancia,
            "valor_servicio"=>"0.0",
            "iva"=>"0.0",
            "total"=>"2000.00",
            "anicipo"=>"0.0",
            "por_cobrar"=>"2000.00",
            "id_forma_pago"=>1,
            "num_unidades"=>1,
            "foraneo"=>"si",
            "total_personas"=>$personas,
            "id_usuario"=>1,
            "fecha_registro"=>$fechaRegistro

             ]);


          $OrdenIDmax=DB::select("SELECT max(id_orden) as orden FROM ordenes");


          $OrdenID=$OrdenIDmax[0]->orden;



          $tipo_punto_ruta=array(1001,1002,1003);
          $tipos_direcciones=array(1001,1002,1003);



          $direcciones=array($searchInput, $searchInput2);



          for ($i=0; $i < count($direcciones) ; $i++) { 


                      switch ($i) {
                            case 0:
                             $direccion=$direcciones[0];
                             $id_tipo_punto_ruta=$tipo_punto_ruta[0];
                             $id_tipo_direccion=$tipos_direcciones[0];
                             $pais=$pais;
                             $estado=$estado;
                             $municipio=$municipio;
                             $colonia=$colonia;
                             $calle=$calle." ".$numero;
                             $cp=$cp;
                             $latitud=$latitud;
                             $longitud=$longitud;

                            break;
                            case 1:

                             $direccion=$direcciones[1];
                             $id_tipo_punto_ruta=$tipo_punto_ruta[1];
                             $id_tipo_direccion=$tipos_direcciones[1];

                             $pais=$pais2;
                             $estado=$estado2;
                             $municipio=$municipio2;
                             $colonia=$colonia2;
                             $calle=$calle2." ".$numero2;
                             $cp=$cp2;
                             $latitud=$latitud2;
                             $longitud=$longitud2;

                            break;
                        
                        default:
                            // code...
                            break;
                    }


                  //direcciones

   
                DB::table('direcciones')->insert(
                    [
                    "id_orden"=>$OrdenID,
                    "cp"=>$cp,
                    "calle"=>$calle,
                    "colonia"=>$colonia,
                    "municipio"=>$municipio,
                    "estado"=>$estado,
                    "pais"=>$pais,
                    "direccion_completa"=>$direccion,
                    "longitud"=>$longitud,
                    "latitud"=>$latitud,
                    "id_tipo_punto_ruta"=>$id_tipo_punto_ruta,
                    "id_tipo_direcion"=>$id_tipo_direccion,
                    "id_usuario"=>1,
                    "observacion"=>$observaciones1,
                    "activo"=>1 ]);

                  

          }


   $response= new stdClass();

   $response->valida="true";

   echo json_encode($response);

    }


    public function consultaOrdenes(){


        $datos=Array();

        $ordenes=DB::select("SELECT * FROM v_ordenes");

               if(count($ordenes)>0){
                   foreach ($ordenes as $campo) {


                     $datos['data'][]=array(

                    "orden"=>$campo->id_orden,
                    "cliente"=>$campo->nombres." ".$campo->apellidos,
                    "telefono"=>$campo->telefono,
                    "rfc"=>$campo->rfc,
                    "unidad"=>$campo->unidad_negocio,
                    "fechasalida"=>$campo->fecha_salida,
                    "horasalida"=>$campo->hora_salida,
                    "fecharegreso"=>$campo->fecha_regreso,
                    "horaregreso"=>$campo->hora_regreso,
                    "distancia"=>$campo->distancia,
                    "total"=>"$".$campo->total,
                    "ver"=>"<i class='fa fa-eye ver'></i>"


                     );
                  }

              }

               $result = json_encode($datos);

                    return $result;

    }

    public function detalle(){


        $datos=Array();

        $orden=$_REQUEST['orden'];

        $direccion1=DB::select("SELECT * FROM direcciones WHERE id_orden=$orden and id_tipo_direcion=1001");
        $direccion2=DB::select("SELECT * FROM direcciones WHERE id_orden=$orden and id_tipo_direcion=1002");

   

         $response= new stdClass();

         $response->direccionInicial=$direccion1[0]->direccion_completa;
         $response->direccionFinal=$direccion2[0]->direccion_completa;

         echo json_encode($response);

              

    }

  
        

   


   
}
