{{--
  Botão Download no início da página + modal (o quê exportar + formato).

  @include('exportacao::partials.botoes-exportar', [
      'action' => url('/meu-modulo/exportar'),
      'formatos' => ['pdf','xlsx','csv','html'],
      'itens' => [ ['value'=>'lista','label'=>'Lista completa'], ['value'=>'resumo','label'=>'Resumo'] ],
      'campoExtra' => ['q' => $busca ?? ''],
      'rotulo' => 'Download',
  ])

  Preferir em @section('acoes-topo') para ficar no começo da tela.
  Opcional: 'moduloId' => 'transporte' — exibe botão Informes (setor/rede) ao lado.
--}}
@php
    $formatos = $formatos ?? ['pdf', 'xlsx', 'csv', 'html'];
    $action = $action ?? '#';
    $campoExtra = $campoExtra ?? [];
    // Não usar $itens — conflita com listas de catálogo nos módulos.
    $opcoes = $opcoes ?? [
        ['value' => 'tela', 'label' => 'Dados desta tela'],
    ];
    $rotulo = $rotulo ?? 'Download';
    $modalId = $modalId ?? ('export-modal-'.substr(md5($action.($tituloModal ?? 'export')), 0, 10));
    $tituloModal = $tituloModal ?? 'Baixar documento';
    $leadModal = $leadModal ?? 'Escolha o conteúdo e o formato. PDF usa o modelo oficial de declaração (cabeçalho, borda, logos e QR).';
    $formatoPadrao = $formatoPadrao ?? ($formatos[0] ?? 'pdf');
    $classeBotao = $classeBotao ?? 'botao icone primaria';
    $moduloId = $moduloId ?? null;
@endphp

<button type="button"
        class="{{ $classeBotao }}"
        data-buriti-modal="{{ $modalId }}"
        title="{{ $rotulo }}"
        aria-label="{{ $rotulo }}">
    <i class="fa fa-download" aria-hidden="true"></i>
</button>
@if(View::exists('buriti-ui::informes-modulo'))
    @include('buriti-ui::informes-modulo', ['moduloId' => $moduloId ?? null])
@endif

<div class="buriti-modal-backdrop" id="{{ $modalId }}" hidden role="dialog" aria-modal="true" aria-labelledby="{{ $modalId }}-titulo">
    <div class="buriti-modal">
        <header>
            <div>
                <h2 id="{{ $modalId }}-titulo"><i class="fa fa-download"></i> {{ $tituloModal }}</h2>
                <p>{{ $leadModal }}</p>
            </div>
            <button type="button" class="buriti-modal-fechar" data-buriti-fechar aria-label="Fechar">
                <i class="fa fa-times"></i>
            </button>
        </header>
        <div class="buriti-modal-corpo">
            <form method="post" action="{{ $action }}" class="form-grid">
                @csrf
                @foreach($campoExtra as $nome => $valor)
                    <input type="hidden" name="{{ $nome }}" value="{{ $valor }}">
                @endforeach

                <label class="col-wide">O que exportar
                    <select name="item" required>
                        @foreach($opcoes as $opcao)
                            @php
                                $valor = is_array($opcao) ? ($opcao['value'] ?? $opcao['id'] ?? '') : (string) $opcao;
                                $label = is_array($opcao) ? ($opcao['label'] ?? $opcao['nome'] ?? $valor) : (string) $opcao;
                            @endphp
                            <option value="{{ $valor }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label>Formato
                    <select name="formato" required>
                        @foreach($formatos as $f)
                            <option value="{{ $f }}" @selected($f === $formatoPadrao)>{{ strtoupper($f) }}</option>
                        @endforeach
                    </select>
                </label>

                <label>Validação / QR
                    <select name="validacao">
                        <option value="1" selected>Sim — código e QR no PDF</option>
                        <option value="0">Não</option>
                    </select>
                </label>

                <div class="acoes">
                    <button type="button" class="botao claro" data-buriti-fechar>Cancelar</button>
                    <button class="botao" type="submit"><i class="fa fa-check"></i> Baixar</button>
                </div>
            </form>
        </div>
    </div>
</div>
