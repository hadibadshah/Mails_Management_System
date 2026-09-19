// Hadi Digital SweetAlert2 helpers with auto-dismiss in 2.5s and Roman Urdu / English text

declare global {
  interface Window {
    Swal: any;
  }
}

export function getSwal() {
  return typeof window !== 'undefined' && window.Swal ? window.Swal : null;
}

export async function cyberAlertSuccess(title: string, html: string) {
  const Swal = getSwal();
  if (Swal) {
    return Swal.fire({
      customClass: {
        popup: 'cyber-swal'
      },
      icon: 'success',
      title,
      html,
      showConfirmButton: false,
      timer: 2500,
      timerProgressBar: true
    });
  } else {
    alert(`${title}: ${html.replace(/<[^>]*>?/gm, '')}`);
  }
}

export async function cyberAlertError(title: string, text: string) {
  const Swal = getSwal();
  if (Swal) {
    return Swal.fire({
      customClass: {
        popup: 'cyber-swal'
      },
      icon: 'error',
      title,
      text,
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    });
  } else {
    alert(`${title}: ${text}`);
  }
}

export async function cyberPromptPin(domain: string, quantity: number, format: string): Promise<string | null> {
  const Swal = getSwal();
  if (!Swal) {
    const entered = prompt(`Security PIN darj karein (${quantity} mails - ${domain}):`);
    return entered;
  }

  const result = await Swal.fire({
    customClass: {
      popup: 'cyber-swal'
    },
    title: 'SECURITY PIN DARJ KAREIN',
    html: `
      <div class="text-xs text-slate-300 space-y-2 mb-2 text-center">
        <p>Mails Extract Karne Ke Liye 4-Digit Security PIN Likhein:</p>
        <div class="bg-slate-950/80 p-2 rounded-lg border border-slate-800 text-[11px] font-mono text-cyan-300">
          Domain: <b class="text-white">${domain}</b> &bull; Quantity: <b class="text-white">${quantity}</b> &bull; Format: <b class="text-white uppercase">${format}</b>
        </div>
      </div>
    `,
    input: 'password',
    inputPlaceholder: '••••',
    inputAttributes: {
      maxlength: '4',
      autocapitalize: 'off',
      autocorrect: 'off',
      inputmode: 'numeric',
      id: 'swal-pin-input'
    },
    showCancelButton: true,
    confirmButtonText: 'UNLOCK & EXTRACT',
    confirmButtonColor: '#06b6d4',
    cancelButtonText: 'Cancel',
    cancelButtonColor: '#334155',
    focusConfirm: false,
    preConfirm: (value: string) => {
      if (!value || value.length !== 4) {
        Swal.showValidationMessage('Barah-e-karam 4-digit security PIN likhein');
        return false;
      }
      return value;
    }
  });

  if (result.isConfirmed && result.value) {
    return result.value as string;
  }
  return null;
}

