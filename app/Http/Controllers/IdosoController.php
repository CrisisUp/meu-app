<?php

namespace App\Http\Controllers;

use App\Models\Idoso;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class IdosoController extends Controller
{
    /**
     * Exibe uma prévia do relatório e permite escolher mês/ano.
     */
    public function relatorioPreview(Idoso $idoso, Request $request)
    {
        $mes = (int) $request->input('mes', date('m'));
        $ano = (int) $request->input('ano', date('Y'));

        $frequencias = $idoso->frequencias()
            ->whereYear('data', $ano)
            ->whereMonth('data', $mes)
            ->orderBy('data')
            ->get();

        $mesNome = Carbon::createFromDate($ano, $mes, 1)->locale('pt_BR')->monthName;

        return view('idosos.relatorio-preview', compact('idoso', 'frequencias', 'mesNome', 'mes', 'ano'));
    }

    /**
     * Gera o relatório mensal de frequência em PDF.
     */
    public function gerarRelatorio(Idoso $idoso, Request $request)
    {
        $mes = (int) $request->input('mes', date('m'));
        $ano = (int) $request->input('ano', date('Y'));

        $frequencias = $idoso->frequencias()
            ->whereYear('data', $ano)
            ->whereMonth('data', $mes)
            ->orderBy('data')
            ->get();

        $mesNome = Carbon::createFromDate($ano, $mes, 1)->locale('pt_BR')->monthName;

        $pdf = Pdf::loadView('idosos.relatorio-pdf', [
            'idoso' => $idoso,
            'frequencias' => $frequencias,
            'mesNome' => ucfirst($mesNome),
            'mes' => $mes,
            'ano' => $ano
        ]);

        return $pdf->download("frequencia-{$idoso->nome}-{$mesNome}-{$ano}.pdf");
    }

    /**
     * Exporta a lista de idosos para CSV respeitando os filtros ativos.
     */
    public function exportarCsv(Request $request)
    {
        $search = $request->input('search');
        $filtro = $request->input('filtro');

        $idosos = Idoso::query()
            ->when($search, function ($query, $search) {
                return $query->where('nome', 'like', "%{$search}%")
                             ->orWhere('cpf', 'like', "%{$search}%");
            })
            ->when($filtro == 'sem_cpf', function ($query) {
                return $query->whereNull('cpf')->orWhere('cpf', '');
            })
            ->when($filtro == 'com_medicamento', function ($query) {
                return $query->whereNotNull('medicamentos')->where('medicamentos', '!=', '');
            })
            ->orderBy('nome')
            ->get();

        $fileName = 'idosos-cdi-' . date('Y-m-d') . '.csv';

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($idosos) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); 
            
            fputcsv($file, ['ID', 'Nome', 'CPF', 'Data Nascimento', 'Idade', 'Responsável', 'Telefone', 'Medicamentos', 'Alergias']);

            foreach ($idosos as $idoso) {
                fputcsv($file, [
                    $idoso->id,
                    $idoso->nome,
                    $idoso->cpf,
                    $idoso->data_nascimento,
                    \Carbon\Carbon::parse($idoso->data_nascimento)->age,
                    $idoso->contato_emergencia_nome,
                    $idoso->contato_emergencia_telefone,
                    $idoso->medicamentos,
                    $idoso->alergias,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $filtro = $request->input('filtro');

        $idosos = Idoso::query()
            ->when($search, function ($query, $search) {
                return $query->where('nome', 'like', "%{$search}%")
                             ->orWhere('cpf', 'like', "%{$search}%");
            })
            ->when($filtro == 'sem_cpf', function ($query) {
                return $query->whereNull('cpf')->orWhere('cpf', '');
            })
            ->when($filtro == 'com_medicamento', function ($query) {
                return $query->whereNotNull('medicamentos')->where('medicamentos', '!=', '');
            })
            ->orderBy('nome')
            ->paginate(10)
            ->withQueryString();

        return view('idosos.index', compact('idosos', 'search', 'filtro'));
    }

    public function create()
    {
        return view('idosos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'data_nascimento' => 'required|date',
            'cpf' => 'nullable|string|max:14|unique:idosos,cpf',
            'nis' => 'required|string|max:14|unique:idosos,nis',
            'contato_emergencia_nome' => 'required|string|max:255',
            'contato_emergencia_telefone' => 'required|string|max:20',
            'alergias' => 'nullable|string',
            'medicamentos' => 'nullable|string',
            'observacoes' => 'nullable|string',
        ]);

        $data = $request->all();

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('fotos_idosos', 'public');
        }

        Idoso::create($data);

        return redirect()->route('idoso.index')->with('success', 'Idoso cadastrado com sucesso!');
    }

    public function show(Idoso $idoso)
    {
        return view('idosos.show', compact('idoso'));
    }

    public function edit(Idoso $idoso)
    {
        return view('idosos.edit', compact('idoso'));
    }

    public function update(Request $request, Idoso $idoso)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'data_nascimento' => 'required|date',
            'cpf' => 'nullable|string|max:14|unique:idosos,cpf,' . $idoso->id,
            'nis' => 'required|string|max:14|unique:idosos,nis,' . $idoso->id,
            'contato_emergencia_nome' => 'required|string|max:255',
            'contato_emergencia_telefone' => 'required|string|max:20',
            'alergias' => 'nullable|string',
            'medicamentos' => 'nullable|string',
            'observacoes' => 'nullable|string',
        ]);

        $data = $request->all();

        if ($request->hasFile('foto')) {
            if ($idoso->foto) {
                Storage::disk('public')->delete($idoso->foto);
            }
            $data['foto'] = $request->file('foto')->store('fotos_idosos', 'public');
        }

        $idoso->update($data);

        return redirect()->route('idoso.show', $idoso)->with('success', 'Cadastro atualizado com sucesso!');
    }

    public function destroy(Idoso $idoso)
    {
        if ($idoso->foto) {
            Storage::disk('public')->delete($idoso->foto);
        }
        $idoso->delete();
        return redirect()->route('idoso.index')->with('success', 'Cadastro excluído com sucesso!');
    }
}
