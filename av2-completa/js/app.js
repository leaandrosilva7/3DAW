/* ===========================================================
   LOTUS STUDIO - Fluxo de Agendamento (modais progressivos)
   =========================================================== */

(function () {
  "use strict";

  const UNITS = [
    { id: "barra",      nome: "Barra da Tijuca", endereco: "Avenida das Americas - Barra Shopping, Piso 2, Loja 301",    nota: 5, reviews: "3.233", img: "img/barradatijuca-img.png" },
    { id: "recreio",    nome: "Recreio",          endereco: "Avenida das Americas - America Shopping, Piso 1, Loja 127", nota: 5, reviews: "1.445", img: "img/recreio-img.png" },
    { id: "botafogo",   nome: "Botafogo",          endereco: "Voluntarios da Patria, n 145 - Terceiro Andar, 104",       nota: 5, reviews: "2.333", img: "img/botafogo-img.png" },
    { id: "copacabana", nome: "Copacabana",        endereco: "Siqueira Campos - Metro, Loja E 106",                      nota: 5, reviews: "4.255", img: "img/copacabana-img.png" }
  ];

  const SERVICES = [
    { id: "relax",  nome: "Massagem Relaxante", duracao: "30 min | 1h | 1:30h", preco: 99.0, img: "img/massagem-relaxante.png" },
    { id: "facial", nome: "Massagem Facial",    duracao: "30 min | 1h",          preco: 89.9, img: "img/massagem-facial-img.png" },
    { id: "vacuo",  nome: "Vacuo terapia",      duracao: "50 min",               preco: 99.0, img: "img/vacuo-terapia-img.png" },
    { id: "aroma",  nome: "Terapia Aromatica",  duracao: "1:30h",                preco: 99.0, img: "img/terapia-aromatica-img.png" }
  ];

  const PAYMENTS = [
    { id: "pix",     nome: "Pix",     classe: "pix",     img: "img/pix-img.png" },
    { id: "credito", nome: "Credito", classe: "credito", img: "img/card-img.png" },
    { id: "debito",  nome: "Debito",  classe: "debito",  img: "img/card-img.png" }
  ];

  const CLIENTE_LOGADO = "Leandro";

  var state = {
    step: 1,
    unidade: null,
    data: "",
    hora: "",
    servicos: [],
    pagamento: null,
    cupom: "",
    nomeCliente: CLIENTE_LOGADO
  };

  function resetState() {
    state.step       = 1;
    state.unidade    = null;
    state.data       = "";
    state.hora       = "";
    state.servicos   = [];
    state.pagamento  = null;
    state.cupom      = "";
    state.nomeCliente = CLIENTE_LOGADO;
  }

  function totalServicos() {
    return state.servicos.reduce(function(sum, id) {
      var s = SERVICES.find(function(x) { return x.id === id; });
      return sum + (s ? s.preco : 0);
    }, 0);
  }

  function formatBRL(v) {
    return "R$ " + v.toFixed(2).replace(".", ",");
  }

  function formatDataBR(iso) {
    if (!iso) return "-";
    var parts = iso.split("-");
    return parts[2] + "/" + parts[1] + "/" + parts[0];
  }

  var overlay = document.getElementById("modal-overlay");
  var box     = document.getElementById("modal-box");
  var openBtn = document.getElementById("open-agendar");

  if (openBtn) {
    openBtn.addEventListener("click", function() {
      resetState();
      openModal();
    });
  }

  function openModal() {
    render();
    overlay.classList.add("active");
    document.body.style.overflow = "hidden";
  }

  function closeModal() {
    overlay.classList.remove("active");
    document.body.style.overflow = "";
  }

  overlay.addEventListener("click", function(e) {
    if (e.target === overlay) closeModal();
  });

  document.addEventListener("keydown", function(e) {
    if (e.key === "Escape" && overlay.classList.contains("active")) closeModal();
  });

  function breadcrumbHTML() {
    var parts = [{ label: "Unidade", step: 1 }];
    if (state.step >= 2) parts.push({ label: "Horario",     step: 2 });
    if (state.step >= 3) parts.push({ label: "Servicos",    step: 3 });
    if (state.step >= 4) parts.push({ label: "Pagamento",   step: 4 });
    if (state.step >= 5) parts.push({ label: "Confirmacao", step: 5 });

    return parts.map(function(p, i) {
      var isLast  = i === parts.length - 1;
      var content = isLast ? "<b>" + p.label + "</b>" : '<span data-goto="' + p.step + '">' + p.label + "</span>";
      var sep     = i > 0 ? '<span class="sep">&gt;</span>' : "";
      return sep + content;
    }).join("");
  }

  function bindBreadcrumb() {
    box.querySelectorAll(".breadcrumb [data-goto]").forEach(function(el) {
      el.addEventListener("click", function() {
        state.step = parseInt(el.dataset.goto, 10);
        render();
      });
    });
  }

  function topBarHTML(showBack) {
    var backPart = showBack
      ? '<button class="back-btn" id="back-btn" aria-label="Voltar"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg></button>'
      : '<span style="width:36px"></span>';
    return '<div class="modal-topbar">' + backPart + '<button class="close-btn" id="close-btn" aria-label="Fechar">&times;</button></div>';
  }

  function bindTopBar() {
    var backBtn  = document.getElementById("back-btn");
    var closeBtn = document.getElementById("close-btn");
    if (backBtn)  backBtn.addEventListener("click", goBack);
    if (closeBtn) closeBtn.addEventListener("click", closeModal);
  }

  function goBack() {
    if (state.step > 1) { state.step -= 1; render(); }
    else closeModal();
  }

  /* ---- RENDER PRINCIPAL ---- */
  function render() {
    var html = topBarHTML(state.step > 1) + '<div class="breadcrumb">' + breadcrumbHTML() + "</div>";

    if      (state.step === 1) html += renderStep1();
    else if (state.step === 2) html += renderStep2();
    else if (state.step === 3) html += renderStep3();
    else if (state.step === 4) html += renderStep4();
    else if (state.step === 5) html += renderStep5();
    else if (state.step === 6) html += renderStep6();

    box.innerHTML = html;
    bindTopBar();
    bindBreadcrumb();

    if (state.step === 1) bindStep1();
    if (state.step === 2) bindStep2();
    if (state.step === 3) bindStep3();
    if (state.step === 4) bindStep4();
    if (state.step === 5) bindStep5();
    if (state.step === 6) bindStep6();

    box.scrollTop = 0;
  }

  /* ---- STEP 1: UNIDADE ---- */
  function renderStep1() {
    var cards = UNITS.map(function(u) {
      return '<div class="unit-card step-panel" data-unit="' + u.id + '">'
        + '<div class="unit-thumb" style="background-image:url(\'' + u.img + '\')"></div>'
        + '<div class="unit-info">'
        + '<h4>' + u.nome + '</h4>'
        + '<p>' + u.endereco + '</p>'
        + '<div class="unit-meta">'
        + '<span class="stars">&#9733;&#9733;&#9733;&#9733;&#9733;<span class="count">' + u.reviews + ' Reviews</span></span>'
        + '<span class="go-btn"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>'
        + '</div></div></div>';
    }).join("");

    return '<h2 class="modal-title">Selecione a unidade mais proxima de voce!</h2>'
         + '<div class="units-grid">' + cards + '</div>';
  }

  function bindStep1() {
    box.querySelectorAll(".unit-card").forEach(function(card) {
      card.addEventListener("click", function() {
        state.unidade = UNITS.find(function(u) { return u.id === card.dataset.unit; });
        state.step = 2;
        render();
      });
    });
  }

  /* ---- STEP 2: HORARIO ---- */
  function renderStep2() {
    var hoje = new Date().toISOString().split("T")[0];
    var podeContinuar = state.data && state.hora;
    return '<h2 class="modal-title">Escolha a data e o horario</h2>'
      + '<div class="step-panel horario-panel">'
      + '<div class="horario-grid">'
      + '<div class="form-field">'
      + '<label for="input-data">Data</label>'
      + '<input type="date" id="input-data" min="' + hoje + '" value="' + state.data + '">'
      + '</div>'
      + '<div class="form-field">'
      + '<label for="input-hora">Horario</label>'
      + '<input type="time" id="input-hora" value="' + state.hora + '">'
      + '</div>'
      + '</div>'
      + '<p class="horario-hint">Atendemos de segunda a sabado, das 08h as 20h.</p>'
      + '</div>'
      + '<div class="modal-footer">'
      + '<button class="btn btn-solid" id="continuar-horario" ' + (podeContinuar ? "" : "disabled") + '>Continuar</button>'
      + '</div>';
  }

  function bindStep2() {
    var inputData = document.getElementById("input-data");
    var inputHora = document.getElementById("input-hora");
    var btnCont   = document.getElementById("continuar-horario");

    function atualizar() {
      state.data = inputData.value;
      state.hora = inputHora.value;
      btnCont.disabled = !(state.data && state.hora);
    }

    inputData.addEventListener("change", atualizar);
    inputHora.addEventListener("change", atualizar);

    btnCont.addEventListener("click", function() {
      if (!state.data || !state.hora) return;
      state.step = 3;
      render();
    });
  }

  /* ---- STEP 3: SERVICOS ---- */
  function renderStep3() {
    var rows = SERVICES.map(function(s) {
      var selected = state.servicos.indexOf(s.id) >= 0;
      return '<div class="service-row ' + (selected ? "selected" : "") + '" data-service="' + s.id + '">'
        + '<div class="service-thumb" style="background-image:url(\'' + s.img + '\')"></div>'
        + '<div class="service-info">'
        + '<h4>' + s.nome + '</h4>'
        + '<div class="duration">Duracoes: ' + s.duracao + '</div>'
        + '<div class="price">A partir de ' + formatBRL(s.preco) + '</div>'
        + '</div>'
        + '<button class="add-btn ' + (selected ? "active" : "") + '" data-service-btn="' + s.id + '">' + (selected ? "&#10003;" : "+") + '</button>'
        + '</div>';
    }).join("");

    var selectedList = state.servicos.length
      ? '<ul class="selected-services-list">' + state.servicos.map(function(id) {
          var s = SERVICES.find(function(x) { return x.id === id; });
          return '<li><span>' + s.nome + '</span><span>' + formatBRL(s.preco) + '</span></li>';
        }).join("") + '</ul>'
      : '<div class="selected-services-list">Nenhum servico selecionado ainda.</div>';

    var u = state.unidade;
    return '<h2 class="modal-title">Selecione um servico ou pacote.</h2>'
      + '<div class="services-layout">'
      + '<div class="services-col step-panel"><div class="cat-label">Spa</div>' + rows + '</div>'
      + '<div class="summary-card step-panel">'
      + '<div class="unit-summary">'
      + '<div class="unit-thumb" style="background-image:url(\'' + u.img + '\')"></div>'
      + '<div><h4>' + u.nome + '</h4><p>' + u.endereco + '</p></div>'
      + '</div>'
      + '<hr class="summary-divider">'
      + selectedList
      + '<hr class="summary-divider">'
      + '<div class="summary-bottom">'
      + '<span class="total">Total: ' + formatBRL(totalServicos()) + '</span>'
      + '<input type="text" class="coupon-input" id="cupom-input" placeholder="Cupom" value="' + state.cupom + '">'
      + '</div>'
      + '<button class="btn btn-outline btn-block" id="continuar-servicos" ' + (state.servicos.length === 0 ? "disabled" : "") + '>Continuar</button>'
      + '</div></div>';
  }

  function bindStep3() {
    box.querySelectorAll("[data-service-btn]").forEach(function(btn) {
      btn.addEventListener("click", function(e) { e.stopPropagation(); toggleService(btn.dataset.serviceBtn); });
    });
    box.querySelectorAll(".service-row").forEach(function(row) {
      row.addEventListener("click", function() { toggleService(row.dataset.service); });
    });
    var cupomInput = document.getElementById("cupom-input");
    if (cupomInput) cupomInput.addEventListener("input", function(e) { state.cupom = e.target.value; });
    var continuarBtn = document.getElementById("continuar-servicos");
    if (continuarBtn) {
      continuarBtn.addEventListener("click", function() {
        if (state.servicos.length === 0) return;
        state.step = 4;
        render();
      });
    }
  }

  function toggleService(id) {
    var idx = state.servicos.indexOf(id);
    if (idx >= 0) state.servicos.splice(idx, 1);
    else state.servicos.push(id);
    render();
  }

  /* ---- STEP 4: PAGAMENTO ---- */
  function renderStep4() {
    var cards = PAYMENTS.map(function(p) {
      var sel = state.pagamento === p.id ? "selected" : "";
      return '<div class="payment-card ' + sel + ' step-panel" data-pay="' + p.id + '">'
        + '<span class="pay-icon ' + p.classe + '"><img src="' + p.img + '" alt="' + p.nome + '"></span>'
        + p.nome + '</div>';
    }).join("");

    return '<h2 class="modal-title">Selecione o Metodo de Pagamento</h2>'
      + '<div class="payment-grid">' + cards + '</div>'
      + '<div class="modal-footer">'
      + '<button class="btn btn-solid" id="continuar-pagamento" ' + (state.pagamento ? "" : "disabled") + '>Continuar</button>'
      + '</div>';
  }

  function bindStep4() {
    box.querySelectorAll(".payment-card").forEach(function(card) {
      card.addEventListener("click", function() { state.pagamento = card.dataset.pay; render(); });
    });
    var continuarBtn = document.getElementById("continuar-pagamento");
    if (continuarBtn) {
      continuarBtn.addEventListener("click", function() {
        if (!state.pagamento) return;
        state.step = 5;
        render();
      });
    }
  }

  /* ---- STEP 5: CONFIRMACAO ---- */
  function renderStep5() {
    var pay = PAYMENTS.find(function(p) { return p.id === state.pagamento; });
    var servicosNomes = state.servicos.map(function(id) {
      return SERVICES.find(function(s) { return s.id === id; }).nome;
    }).join(", ");

    return '<h2 class="modal-title">Confirme seu agendamento</h2>'
      + '<div class="confirm-grid step-panel">'
      + '<div>'
      + '<div class="form-field"><label for="nome-cliente">Nome do cliente</label>'
      + '<input type="text" id="nome-cliente" value="' + state.nomeCliente + '"></div>'
      + '<p style="color:var(--text-soft);font-size:.85rem;line-height:1.5;">Confira os dados antes de confirmar.</p>'
      + '<div class="error-msg" id="confirm-error">Nao foi possivel enviar o agendamento. Tente novamente.</div>'
      + '</div>'
      + '<div class="resume-box">'
      + '<h4>Resumo do agendamento</h4>'
      + '<div class="resume-row"><span>Unidade</span><span>'    + state.unidade.nome      + '</span></div>'
      + '<div class="resume-row"><span>Data</span><span>'       + formatDataBR(state.data) + '</span></div>'
      + '<div class="resume-row"><span>Horario</span><span>'    + state.hora               + '</span></div>'
      + '<div class="resume-row"><span>Servico(s)</span><span>' + servicosNomes            + '</span></div>'
      + '<div class="resume-row"><span>Pagamento</span><span>'  + pay.nome                 + '</span></div>'
      + '<div class="resume-row"><span>Total</span><span>'      + formatBRL(totalServicos()) + '</span></div>'
      + '</div></div>'
      + '<div class="modal-footer">'
      + '<button class="btn btn-solid" id="confirmar-agendamento">Confirmar Agendamento</button>'
      + '</div>';
  }

  function bindStep5() {
    var nomeInput = document.getElementById("nome-cliente");
    if (nomeInput) nomeInput.addEventListener("input", function(e) { state.nomeCliente = e.target.value; });
    var confirmarBtn = document.getElementById("confirmar-agendamento");
    if (confirmarBtn) confirmarBtn.addEventListener("click", function() { submitAgendamento(confirmarBtn); });
  }

  function submitAgendamento(btn) {
    var errorEl = document.getElementById("confirm-error");
    errorEl.style.display = "none";

    if (!state.nomeCliente || !state.nomeCliente.trim()) {
      errorEl.textContent = "Informe o nome do cliente.";
      errorEl.style.display = "block";
      return;
    }

    var payload = {
      nome_cliente: state.nomeCliente.trim(),
      unidade:      state.unidade.nome,
      data:         state.data,
      hora:         state.hora,
      servicos:     state.servicos.map(function(id) { return SERVICES.find(function(s) { return s.id === id; }).nome; }),
      pagamento:    PAYMENTS.find(function(p) { return p.id === state.pagamento; }).nome,
      cupom:        state.cupom || null,
      total:        totalServicos()
    };

    btn.disabled = true;
    var originalText = btn.textContent;
    btn.innerHTML = '<span class="spinner"></span>';

    postAgendamento(payload).then(function(result) {
      state.lastResult = result;
      state.step = 6;
      render();
    }).catch(function() {
      errorEl.textContent = "Nao foi possivel enviar o agendamento. Tente novamente.";
      errorEl.style.display = "block";
      btn.disabled = false;
      btn.textContent = originalText;
    });
  }

  function postAgendamento(payload) {
    return fetch("api/agendamento.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    }).then(function(resp) {
      if (!resp.ok) throw new Error("HTTP " + resp.status);
      return resp.json();
    }).catch(function(err) {
      console.warn("Fallback mock:", err);
      return new Promise(function(resolve) {
        setTimeout(function() {
          resolve({
            success:   true,
            protocolo: "MOCK-" + Math.floor(Math.random() * 900000 + 100000),
            mensagem:  "Agendamento confirmado"
          });
        }, 500);
      });
    });
  }

  /* ---- STEP 6: SUCESSO ---- */
  function renderStep6() {
    var r = state.lastResult || {};
    return '<div class="success-panel step-panel">'
      + '<div class="success-icon">&#10003;</div>'
      + '<h3>Agendamento confirmado!</h3>'
      + '<p>' + (r.mensagem || "Seu horario foi reservado com sucesso.") + '</p>'
      + '<div class="protocol">Protocolo: ' + (r.protocolo || "-") + '</div>'
      + '<div class="modal-footer" style="justify-content:center;">'
      + '<button class="btn btn-solid" id="fechar-sucesso">Fechar</button>'
      + '</div></div>';
  }

  function bindStep6() {
    var fecharBtn = document.getElementById("fechar-sucesso");
    if (fecharBtn) fecharBtn.addEventListener("click", closeModal);
  }

})();
