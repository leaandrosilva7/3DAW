/* ===========================================================
   LÓTUS STUDIO — Fluxo de Agendamento (modais progressivos)
   =========================================================== */

(function () {
  "use strict";

  const UNITS = [
    {
      id: "barra",
      nome: "Barra da Tijuca",
      endereco: "Avenida das Américas — Barra Shopping, Piso 2, Loja 301",
      nota: 5,
      reviews: "3.233",
      img: "img/barradatijuca-img.png"
    },
    {
      id: "recreio",
      nome: "Recreio",
      endereco: "Avenida das Américas — America Shopping, Piso 1, Loja 127",
      nota: 5,
      reviews: "1.445",
      img: "img/recreio-img.png"
    },
    {
      id: "botafogo",
      nome: "Botafogo",
      endereco: "Voluntários da Pátria, nº 145 — Terceiro Andar, 104",
      nota: 5,
      reviews: "2.333",
      img: "img/botafogo-img.png"
    },
    {
      id: "copacabana",
      nome: "Copacabana",
      endereco: "Siqueira Campos — Metrô, Loja E 106",
      nota: 5,
      reviews: "4.255",
      img: "img/copacabana-img.png"
    }
  ];

  const SERVICES = [
    { id: "relax", nome: "Massagem Relaxante", duracao: "30 min | 1h | 1:30h", preco: 99.0, img: "img/massagem-relaxante.png" },
    { id: "facial", nome: "Massagem Facial", duracao: "30 min | 1h", preco: 89.9, img: "img/massagem-facial-img.png" },
    { id: "vacuo", nome: "Vácuo terapia", duracao: "50 min", preco: 99.0, img: "img/vacuo-terapia-img.png" },
    { id: "aroma", nome: "Terapia Aromática", duracao: "1:30h", preco: 99.0, img: "img/terapia-aromatica-img.png" }
  ];

const PAYMENTS = [
    { id: "pix", nome: "Pix", classe: "pix", img: "img/pix-img.png" },
    { id: "credito", nome: "Crédito", classe: "credito", img: "img/card-img.png" },
    { id: "debito", nome: "Débito", classe: "debito", img: "img/card-img.png" }
];

  const CLIENTE_LOGADO = "Leandro";

  /* ---------------- ESTADO ---------------- */
  const state = {
    step: 1,
    unidade: null,
    servicos: [],       // array de ids
    pagamento: null,
    cupom: "",
    nomeCliente: CLIENTE_LOGADO
  };

  function resetState() {
    state.step = 1;
    state.unidade = null;
    state.servicos = [];
    state.pagamento = null;
    state.cupom = "";
    state.nomeCliente = CLIENTE_LOGADO;
  }

  function totalServicos() {
    return state.servicos.reduce((sum, id) => {
      const s = SERVICES.find((x) => x.id === id);
      return sum + (s ? s.preco : 0);
    }, 0);
  }

  function formatBRL(v) {
    return "R$ " + v.toFixed(2).replace(".", ",");
  }

  /* ---------------- ELEMENTOS ---------------- */
  const overlay = document.getElementById("modal-overlay");
  const box = document.getElementById("modal-box");
  const openBtn = document.getElementById("open-agendar");

  if (openBtn) {
    openBtn.addEventListener("click", () => {
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

  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) closeModal();
  });
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && overlay.classList.contains("active")) closeModal();
  });

  /* ---------------- BREADCRUMB ---------------- */
  function breadcrumbHTML() {
    const parts = [{ label: "Unidade", step: 1 }];
    if (state.step >= 2) parts.push({ label: "Serviços", step: 2 });
    if (state.step >= 3) parts.push({ label: "Pagamento", step: 3 });
    if (state.step >= 4) parts.push({ label: "Confirmação", step: 4 });

    return parts
      .map((p, i) => {
        const isLast = i === parts.length - 1;
        const content = isLast ? `<b>${p.label}</b>` : `<span data-goto="${p.step}">${p.label}</span>`;
        const sep = i > 0 ? `<span class="sep">&gt;</span>` : "";
        return sep + content;
      })
      .join("");
  }

  function bindBreadcrumb() {
    box.querySelectorAll(".breadcrumb [data-goto]").forEach((el) => {
      el.addEventListener("click", () => {
        state.step = parseInt(el.dataset.goto, 10);
        render();
      });
    });
  }

  function topBarHTML(showBack) {
    return `
      <div class="modal-topbar">
        ${showBack ? `<button class="back-btn" id="back-btn" aria-label="Voltar">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
        </button>` : `<span style="width:36px"></span>`}
        <button class="close-btn" id="close-btn" aria-label="Fechar">&times;</button>
      </div>
    `;
  }

  function bindTopBar() {
    const backBtn = document.getElementById("back-btn");
    const closeBtn = document.getElementById("close-btn");
    if (backBtn) backBtn.addEventListener("click", goBack);
    if (closeBtn) closeBtn.addEventListener("click", closeModal);
  }

  function goBack() {
    if (state.step > 1) {
      state.step -= 1;
      render();
    } else {
      closeModal();
    }
  }

  /* ---------------- RENDER PRINCIPAL ---------------- */
  function render() {
    let html = topBarHTML(state.step > 1) + `<div class="breadcrumb">${breadcrumbHTML()}</div>`;

    if (state.step === 1) html += renderStepUnidade();
    else if (state.step === 2) html += renderStepServicos();
    else if (state.step === 3) html += renderStepPagamento();
    else if (state.step === 4) html += renderStepConfirmacao();
    else if (state.step === 5) html += renderStepSucesso();

    box.innerHTML = html;
    bindTopBar();
    bindBreadcrumb();

    if (state.step === 1) bindStepUnidade();
    if (state.step === 2) bindStepServicos();
    if (state.step === 3) bindStepPagamento();
    if (state.step === 4) bindStepConfirmacao();
    if (state.step === 5) bindStepSucesso();

    box.scrollTop = 0;
  }

  /* ---------------- STEP 1: UNIDADE ---------------- */
  function renderStepUnidade() {
    const cards = UNITS.map(
      (u) => `
      <div class="unit-card step-panel" data-unit="${u.id}">
        <div class="unit-thumb" style="background-image:url('${u.img}')"></div>
        <div class="unit-info">
          <h4>${u.nome}</h4>
          <p>${u.endereco}</p>
          <div class="unit-meta">
            <span class="stars">★★★★★<span class="count">${u.reviews} Reviews</span></span>
            <span class="go-btn">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </span>
          </div>
        </div>
      </div>`
    ).join("");

    return `
      <h2 class="modal-title">Selecione a unidade mais próxima de você!</h2>
      <div class="units-grid">${cards}</div>
    `;
  }

  function bindStepUnidade() {
    box.querySelectorAll(".unit-card").forEach((card) => {
      card.addEventListener("click", () => {
        state.unidade = UNITS.find((u) => u.id === card.dataset.unit);
        state.step = 2;
        render();
      });
    });
  }

  /* ---------------- STEP 2: SERVIÇOS ---------------- */
  function renderStepServicos() {
    const rows = SERVICES.map((s) => {
      const selected = state.servicos.includes(s.id);
      return `
      <div class="service-row ${selected ? "selected" : ""}" data-service="${s.id}">
        <div class="service-thumb" style="background-image:url('${s.img}')"></div>
        <div class="service-info">
          <h4>${s.nome}</h4>
          <div class="duration">Durações: ${s.duracao}</div>
          <div class="price">A partir de ${formatBRL(s.preco)}</div>
        </div>
        <button class="add-btn ${selected ? "active" : ""}" data-service-btn="${s.id}" aria-label="Adicionar">${selected ? "✓" : "+"}</button>
      </div>`;
    }).join("");

    const selectedList = state.servicos.length
      ? `<ul class="selected-services-list">${state.servicos
          .map((id) => {
            const s = SERVICES.find((x) => x.id === id);
            return `<li><span>${s.nome}</span><span>${formatBRL(s.preco)}</span></li>`;
          })
          .join("")}</ul>`
      : `<div class="selected-services-list">Nenhum serviço selecionado ainda.</div>`;

    const u = state.unidade;

    return `
      <h2 class="modal-title">Selecione um serviço ou pacote.</h2>
      <div class="services-layout">
        <div class="services-col step-panel">
          <div class="cat-label">Spa</div>
          ${rows}
        </div>
        <div class="summary-card step-panel">
          <div class="unit-summary">
            <div class="unit-thumb" style="background-image:url('${u.img}')"></div>
            <div>
              <h4>${u.nome}</h4>
              <p>${u.endereco}</p>
            </div>
            <span class="lotus-icon">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 21c-4-2-7-5-7-9a7 7 0 0 1 14 0c0 4-3 7-7 9Z"/></svg>
            </span>
          </div>
          <hr class="summary-divider">
          ${selectedList}
          <hr class="summary-divider">
          <div class="summary-bottom">
            <span class="total">Total: ${formatBRL(totalServicos())}</span>
            <input type="text" class="coupon-input" id="cupom-input" placeholder="Cupom" value="${state.cupom}">
          </div>
          <button class="btn btn-outline btn-block" id="continuar-servicos" ${state.servicos.length === 0 ? "disabled" : ""}>Continuar</button>
        </div>
      </div>
    `;
  }

  function bindStepServicos() {
    box.querySelectorAll("[data-service-btn]").forEach((btn) => {
      btn.addEventListener("click", (e) => {
        e.stopPropagation();
        toggleService(btn.dataset.serviceBtn);
      });
    });
    box.querySelectorAll(".service-row").forEach((row) => {
      row.addEventListener("click", () => toggleService(row.dataset.service));
    });
    const cupomInput = document.getElementById("cupom-input");
    if (cupomInput) cupomInput.addEventListener("input", (e) => (state.cupom = e.target.value));

    const continuarBtn = document.getElementById("continuar-servicos");
    if (continuarBtn) {
      continuarBtn.addEventListener("click", () => {
        if (state.servicos.length === 0) return;
        state.step = 3;
        render();
      });
    }
  }

  function toggleService(id) {
    const idx = state.servicos.indexOf(id);
    if (idx >= 0) state.servicos.splice(idx, 1);
    else state.servicos.push(id);
    render();
  }

  /* ---------------- STEP 3: PAGAMENTO ---------------- */
  function renderStepPagamento() {
    const cards = PAYMENTS.map(
      (p) => `
      <div class="payment-card ${state.pagamento === p.id ? "selected" : ""} step-panel" data-pay="${p.id}">
        <span class="pay-icon ${p.classe}">${p.img ? `<img src="${p.img}" alt="${p.nome}">` : p.icone}</span>
        ${p.nome}
      </div>`
    ).join("");

    return `
      <h2 class="modal-title">Selecione o Método de Pagamento</h2>
      <div class="payment-grid">${cards}</div>
      <div class="modal-footer">
        <button class="btn btn-solid" id="continuar-pagamento" ${state.pagamento ? "" : "disabled"}>Continuar</button>
      </div>
    `;
  }

  function bindStepPagamento() {
    box.querySelectorAll(".payment-card").forEach((card) => {
      card.addEventListener("click", () => {
        state.pagamento = card.dataset.pay;
        render();
      });
    });
    const continuarBtn = document.getElementById("continuar-pagamento");
    if (continuarBtn) {
      continuarBtn.addEventListener("click", () => {
        if (!state.pagamento) return;
        state.step = 4;
        render();
      });
    }
  }

  /* ---------------- STEP 4: CONFIRMAÇÃO ---------------- */
  function renderStepConfirmacao() {
    const pay = PAYMENTS.find((p) => p.id === state.pagamento);
    const servicosNomes = state.servicos.map((id) => SERVICES.find((s) => s.id === id).nome).join(", ");

    return `
      <h2 class="modal-title">Confirme seu agendamento</h2>
      <div class="confirm-grid step-panel">
        <div>
          <div class="form-field">
            <label for="nome-cliente">Nome do cliente</label>
            <input type="text" id="nome-cliente" value="${state.nomeCliente}">
          </div>
          <p style="color:var(--text-soft); font-size:.85rem; line-height:1.5;">
            Confira os dados antes de confirmar. Você poderá acompanhar este agendamento na sua conta.
          </p>
          <div class="error-msg" id="confirm-error">Não foi possível enviar o agendamento. Tente novamente.</div>
        </div>
        <div class="resume-box">
          <h4>Resumo do agendamento</h4>
          <div class="resume-row"><span>Unidade</span><span>${state.unidade.nome}</span></div>
          <div class="resume-row"><span>Serviço(s)</span><span>${servicosNomes}</span></div>
          <div class="resume-row"><span>Pagamento</span><span>${pay.nome}</span></div>
          <div class="resume-row"><span>Total</span><span>${formatBRL(totalServicos())}</span></div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-solid" id="confirmar-agendamento">Confirmar Agendamento</button>
      </div>
    `;
  }

  function bindStepConfirmacao() {
    const nomeInput = document.getElementById("nome-cliente");
    if (nomeInput) nomeInput.addEventListener("input", (e) => (state.nomeCliente = e.target.value));

    const confirmarBtn = document.getElementById("confirmar-agendamento");
    if (confirmarBtn) confirmarBtn.addEventListener("click", () => submitAgendamento(confirmarBtn));
  }

  async function submitAgendamento(btn) {
    const errorEl = document.getElementById("confirm-error");
    errorEl.style.display = "none";

    if (!state.nomeCliente || !state.nomeCliente.trim()) {
      errorEl.textContent = "Informe o nome do cliente.";
      errorEl.style.display = "block";
      return;
    }

    const payload = {
      nome_cliente: state.nomeCliente.trim(),
      unidade: state.unidade.nome,
      servicos: state.servicos.map((id) => SERVICES.find((s) => s.id === id).nome),
      pagamento: PAYMENTS.find((p) => p.id === state.pagamento).nome,
      cupom: state.cupom || null,
      total: totalServicos()
    };

    btn.disabled = true;
    const originalText = btn.textContent;
    btn.innerHTML = `<span class="spinner"></span>`;

    try {
      const result = await postAgendamento(payload);
      state.lastResult = result;
      state.step = 5;
      render();
    } catch (err) {
      errorEl.textContent = "Não foi possível enviar o agendamento. Tente novamente.";
      errorEl.style.display = "block";
      btn.disabled = false;
      btn.textContent = originalText;
    }
  }

  async function postAgendamento(payload) {
    try {
      const resp = await fetch("api/agendamento.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
      });
      if (!resp.ok) throw new Error("HTTP " + resp.status);
      const data = await resp.json();
      return data;
    } catch (err) {
      // Fallback mock — só para visualização do fluxo sem backend ativo.
      console.warn("Falha ao chamar api/agendamento.php — usando resposta simulada.", err);
      await new Promise((r) => setTimeout(r, 500));
      return {
        success: true,
        protocolo: "MOCK-" + Math.floor(Math.random() * 900000 + 100000),
        mensagem: "Agendamento confirmado"
      };
    }
  }

  /* ---------------- STEP 5: SUCESSO ---------------- */
  function renderStepSucesso() {
    const r = state.lastResult || {};
    return `
      <div class="success-panel step-panel">
        <div class="success-icon">✓</div>
        <h3>Agendamento confirmado!</h3>
        <p>${r.mensagem || "Seu horário foi reservado com sucesso."}</p>
        <div class="protocol">Protocolo: ${r.protocolo || "—"}</div>
        <div class="modal-footer" style="justify-content:center;">
          <button class="btn btn-solid" id="fechar-sucesso">Fechar</button>
        </div>
      </div>
    `;
  }

  function bindStepSucesso() {
    const fecharBtn = document.getElementById("fechar-sucesso");
    if (fecharBtn) fecharBtn.addEventListener("click", closeModal);
  }
})();
