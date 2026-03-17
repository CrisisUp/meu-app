<?php

namespace App\Http\Controllers;

use App\Models\Frequencia;
use App\Models\Idoso;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class FrequenciaController extends Controller
{
    /**
     * Exibe a lista de frequência para um dia específico.
     */
    public function index(Request $request)
    {
        $data = $request->input('data', Carbon::today()->toDateString());
        
        // Busca todos os idosos e suas frequências para o dia selecionado
        $idosos = Idoso::orderBy('nome')->get();
        
        $frequencias = Frequencia::where('data', $data)->get()->keyBy('idoso_id');

        return view('frequencias.index', compact('idosos', 'frequencias', 'data'));
    }

    /**
     * Salva a frequência em lote.
     */
    public function store(Request $request)
    {
        $data = $request->input('data');
        $presencas = $request->input('presencas', []); // idosos que estão presentes (marcados no checkbox)
        $observacoes = $request->input('observacoes', []); // notas de intercorrências

        // Buscamos todos os idosos
        $todosIdosos = Idoso::all();

        foreach ($todosIdosos as $idoso) {
            $status = isset($presencas[$idoso->id]) ? 'presente' : 'ausente';
            $obs = $observacoes[$idoso->id] ?? null;
            
            Frequencia::updateOrCreate(
                ['idoso_id' => $idoso->id, 'data' => $data],
                [
                    'status' => $status,
                    'observacoes' => $obs,
                    'user_id' => Auth::id() // Auditoria: quem salvou/alterou
                ]
            );
        }

        return redirect()->route('frequencia.index', ['data' => $data])
                         ->with('success', 'Frequência atualizada com sucesso!');
    }
}
