<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\consulta;
use App\Models\paciente;

use Session;

class consultaController extends Controller
{
    public function index(){

        if (!Auth::user()) {

            Session::put('url', url()->current());    
            return redirect(route('login.index'));
        }

        if(Auth::user()->accesoRuta('/consulta')){

            consulta::actualizarEstados();            

            if(Auth::user()->accesoRuta('/consulta/all')){

               

                $resultado = consulta::get();

            }elseif(Auth::user()->accesoRuta('/paciente/historia/clinica')){   
                         
                
                if (Auth::user()->sucursal) {
                    $resultado = consulta::whereIn('estado_consulta',['Pendiente','EN CURSO'])->where('sucursal_id',Auth::user()->sucursal->id)->orderBy('estado_consulta','DESC')->get();
                } else {
                    $resultado = consulta::where('estado_consulta','Pendiente')->orWhere('estado_consulta','EN CURSO') ->orderBy('estado_consulta','DESC')->get();
                }
                
                

            }else{


                
                if (Auth::user()->sucursal) {
                    $resultado = consulta::whereIn('estado_consulta',['Pendiente','EN CURSO'])->where('sucursal_id',Auth::user()->sucursal->id)->orderBy('estado_consulta','DESC')->get();
                } else {
                    $resultado = consulta::where('estado_consulta','Pendiente')->get();
                }

                

            }          

            return view('consulta.index', ["resultado"=>$resultado]);
            
        }

        return redirect(route('index'));

    }

    public function create2($id){

        if (!Auth::user()) {

            Session::put('url', url()->current());    
            return redirect(route('login.index'));
        }

        if(Auth::user()->accesoRuta('/consulta/create')){

            $obj_consulta = new consulta();        
            $obj_consulta->paciente_id=$id;
            $obj_consulta->estado_consulta = 'Pendiente';
            $obj_consulta->usuario_id = Auth::user()->id;
            $obj_consulta->sucursal_id = Auth::user()->sucursal_id;      
            
            $obj_consulta->save();
            return redirect()->back()->withErrors(['status' => "Se ha creado la consulta para el paciente: " .$obj_consulta->paciente->identificacion_paciente ]);
            
            
        }

        return redirect(route('index'));

    }

    public function insert(Request $request){

        if (!Auth::user()) {

            Session::put('url', url()->current());    
            return redirect(route('login.index'));
        }

        if(Auth::user()->accesoRuta('/consulta/create')){  
            
            $paciente = paciente::where('identificacion_paciente',$request->txtCedula)->first();

            $edad = $paciente->edad();

            if ($edad<18) {

                $obj_consulta->responsable_menor = $request->txtNombre;
                $obj_consulta->parentesco_menor = $request->txtParentesco;
 
            }

            $obj_consulta = new consulta();        
            $obj_consulta->paciente_id=$paciente->id;
            $obj_consulta->estado_consulta = 'Pendiente';
            $obj_consulta->usuario_id = Auth::user()->id;
            $obj_consulta->sucursal_id = Auth::user()->sucursal_id;  

            $obj_consulta->save();



            return redirect()->back()->withErrors(['status' => "Se ha creado la consulta para el paciente: " .$obj_consulta->paciente->identificacion_paciente ]);

        }



    }

    public function menor(Request $request){

        if (!Auth::user()) {

            Session::put('url', url()->current());    
            return redirect(route('login.index'));
        }

        if(Auth::user()->accesoRuta('/consulta/create')){
                        
  
            $obj_consulta = new consulta();        
            $obj_consulta->paciente_id= $request->paciente_id;
            $obj_consulta->responsable_menor = $request->txtNombre;
            $obj_consulta->parentesco_menor = $request->txtParentesco;
            $obj_consulta->estado_consulta = 'Pendiente';   
            $obj_consulta->sucursal_id = Auth::user()->sucursal_id;         
            
            $obj_consulta->save();
            return redirect()->back()->withErrors(['status' => "Se ha creado la consulta para el paciente: " .$obj_consulta->paciente->identificacion_paciente ]);
            
        }

        return redirect(route('index'));

    }

    public function iniciar($id){


        if (!Auth::user()) {

            Session::put('url', url()->current());    
            return redirect(route('login.index'));
        }

        if(Auth::user()->accesoRuta('/consulta')){

            $consulta = consulta::find($id);
            $paciente = paciente::find($consulta->paciente->id);

            return view('consulta.iniciar',['consulta'=>$consulta,'paciente'=>$paciente]);
        }
    }

    public function save(Request $request){

        if (!Auth::user()) {

            Session::put('url', url()->current());    
            return redirect(route('login.index'));
        }

        if(Auth::user()->accesoRuta('/consulta/registrar')){     
            
            $consulta = consulta::find($request->txtConsultaId);
            $consulta->medico_id = Auth::user()->id;
            $consulta->fecha_consulta = $request->txtFecha;
            $consulta->frecuencia_respiratoria = $request->txtFrecR;
            $consulta->frecuencia_cardiaca = $request->txtFrecC;
            $consulta->presion_arterial = $request->txtPresA;
            $consulta->temperatura = $request->txtTemp;
            $consulta->saturacion_oxigeno = $request->txtSatO;
            $consulta->historia_clinica = $request->txtHistoriaClinica;
            $consulta->examen_fisico = $request->txtExamenFisico;
            $consulta->talla = $request->txtTalla;
            $consulta->peso = $request->txtPeso;
            $consulta->laboratorios_examenes = $request->txtLaboratoriosExamenes;
            $consulta->alergias = $request->txtAlergias;
            $consulta->medicinas = $request->txtMedicamentos;
            $consulta->diagnostico = $request->txtDiagnostico;
            $consulta->recomendaciones = $request->txtRecomendaciones;

            $paciente = paciente::find($consulta->paciente_id);

            $paciente->alergias = $request->txtAlergias;
            $paciente->medicinas = $request->txtMedicamentos;


            $paciente->save();

            $consulta->estado_consulta = 'EN CURSO';

            if ($request->accion == 'terminar') {
                $consulta->estado_consulta = 'TERMINADA';
            }

            
            $consulta->save();

            return redirect()->back()->withErrors(['status' => "Se ha guardo la consulta correctamente"]);

        }

        return redirect(route('index'));
    }

    public function delete($id){

        if (!Auth::user()) {

            Session::put('url', url()->current());    
            return redirect(route('login.index'));
        }

        if(Auth::user()->accesoRuta('/consulta/delete')){               


            $consulta = consulta::find($id);
            $consulta->estado_consulta = 'ELIMINADA';
            $consulta->save();

            return redirect()->back()->withErrors(['danger' => "Se ha elimino la consulta correctamente"]);

        }

        return redirect(route('index'));
    }
}
