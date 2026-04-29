/**
 * 공통 AJAX 폼 제출 유틸리티
 * Usage: ajaxSubmit(formEl, { beforeSubmit, onSuccess, onError })
 */
async function ajaxSubmit(formEl, opts = {}) {
  const btn     = formEl.querySelector('[type=submit]');
  const origTxt = btn?.textContent;
  if (btn) { btn.disabled = true; btn.textContent = '처리 중...'; }

  _clearErrors(formEl);

  if (typeof opts.beforeSubmit === 'function') opts.beforeSubmit(formEl);

  try {
    const res  = await fetch(formEl.getAttribute('action') || location.pathname, {
      method:  'POST',
      body:    new FormData(formEl),
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });
    const data = await res.json();

    if (data.ok) {
      if (typeof opts.onSuccess === 'function') opts.onSuccess(data);
      else if (data.redirect) location.href = data.redirect;
    } else {
      if (data.errors)  _showFieldErrors(formEl, data.errors);
      if (data.message) _showToast(formEl, data.message, 'error');
      if (typeof opts.onError === 'function') opts.onError(data);
    }
  } catch {
    _showToast(formEl, '네트워크 오류가 발생했습니다. 다시 시도해 주세요.', 'error');
  } finally {
    if (btn) { btn.disabled = false; if (origTxt) btn.textContent = origTxt; }
  }
}

function _clearErrors(form) {
  form.querySelectorAll('[data-ajax-err]').forEach(el => {
    el.textContent = '';
    el.style.display = 'none';
  });
  form.querySelectorAll('.ajax-inline-err').forEach(el => {
    el.textContent = '';
    el.style.display = 'none';
  });
  form.querySelectorAll('.ajax-form-toast').forEach(el => el.remove());
  form.querySelectorAll('.error[name], input.error, textarea.error, select.error').forEach(el => el.classList.remove('error'));
}

function _showFieldErrors(form, errors) {
  Object.entries(errors).forEach(([field, msg]) => {
    // data-ajax-err="field" 우선, 없으면 id 패턴
    const errEl = form.querySelector(`[data-ajax-err="${field}"]`)
               || form.querySelector(`#${field}Err`)
               || form.querySelector(`#${field}Error`);
    if (errEl) {
      errEl.textContent = msg;
      errEl.style.display = 'block';
    }
    const input = form.querySelector(`[name="${field}"]`);
    if (input) input.classList.add('error');
  });
}

function _showToast(form, msg, type = 'error') {
  const el = document.createElement('div');
  el.className = `ajax-form-toast toast-msg toast-${type}`;
  el.textContent = msg;
  // form-actions 앞에 삽입, 없으면 form 최상단
  const anchor = form.querySelector('.form-actions') || form.firstElementChild;
  anchor ? form.insertBefore(el, anchor) : form.prepend(el);
  if (type !== 'error') setTimeout(() => el.remove(), 4000);
}
