function maskCpf(v) {
  return v.replace(/\D/g, '').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2');
}

function maskPhone(v) {
  v = v.replace(/\D/g, '');
  if (v.length > 10) return v.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
  return v.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
}

$(document).on('input', '.mask-cpf', function() { this.value = maskCpf(this.value); });
$(document).on('input', '.mask-phone', function() { this.value = maskPhone(this.value); });

function generatePassword(length = 12) {
  const upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
  const lower = 'abcdefghijkmnopqrstuvwxyz';
  const nums = '23456789';
  const sym = '!@#$%*?';
  const all = upper + lower + nums + sym;
  let p = upper[Math.floor(Math.random()*upper.length)] + lower[Math.floor(Math.random()*lower.length)] + nums[Math.floor(Math.random()*nums.length)] + sym[Math.floor(Math.random()*sym.length)];
  for (let i = p.length; i < length; i++) p += all[Math.floor(Math.random()*all.length)];
  return p.split('').sort(() => Math.random()-0.5).join('');
}

const FormSubmitGuard = {
  lock(form, submittingText = 'Salvando...') {
    if (!form || form.dataset.submitting === '1') {
      return false;
    }

    form.dataset.submitting = '1';
    const buttons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
    buttons.forEach((btn) => {
      if (!btn.dataset.originalLabel) {
        btn.dataset.originalLabel = btn.tagName.toLowerCase() === 'button' ? btn.innerHTML : btn.value;
      }
      btn.disabled = true;
      if (btn.tagName.toLowerCase() === 'button') {
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' + submittingText;
      } else {
        btn.value = submittingText;
      }
    });

    return true;
  },

  unlock(form) {
    if (!form) {
      return;
    }

    form.dataset.submitting = '0';
    const buttons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
    buttons.forEach((btn) => {
      btn.disabled = false;
      if (btn.dataset.originalLabel) {
        if (btn.tagName.toLowerCase() === 'button') {
          btn.innerHTML = btn.dataset.originalLabel;
        } else {
          btn.value = btn.dataset.originalLabel;
        }
      }
    });
  }
};

window.FormSubmitGuard = FormSubmitGuard;
