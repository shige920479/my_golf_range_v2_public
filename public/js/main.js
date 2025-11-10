document.addEventListener("DOMContentLoaded", () => {
  const targetBtn = document.getElementById("regist-btn");
  const consentBox = document.querySelector("input[name='consent']");

  if (!targetBtn || !consentBox) return;

  targetBtn.disabled = true;
  targetBtn.classList.add("is-inactive");

  consentBox.addEventListener("change", (e) => {
    const isChecked = e.target.checked;

    targetBtn.disabled = !isChecked;
    targetBtn.classList.toggle("is-active", isChecked);
    targetBtn.classList.toggle("is-inactive", !isChecked);
  });
});

//レンタルクラブのモデル自動表示
document.addEventListener("DOMContentLoaded", () => {
  const clubSelect = document.querySelector("#club-select");
  const modelDisplay = document.querySelector("#model-display");

  const updateModelDisplay = () => {
    const selectedOption = clubSelect.options[clubSelect.selectedIndex];
    modelDisplay.textContent = selectedOption.dataset.model;
  };

  if (clubSelect && modelDisplay) {
    updateModelDisplay();
    clubSelect.addEventListener("change", updateModelDisplay);
  }

  //レンタルクラブ・シャワーのチェックを外すとデフォルト表示へ切替
  const checkRental = document.getElementById("rental");
  if (checkRental) {
    checkRental.addEventListener("change", function () {
      if (!this.checked) {
        clearRentalData();
      } else {
        // 何もしない
      }
    });
    const clearRentalData = () => {
      const selectClub = document.getElementById("club-select");
      selectClub.selectedIndex = 0;
      updateModelDisplay();
    };
  }
  const checkShower = document.getElementById("shower");
  if (checkShower) {
    checkShower.addEventListener("change", function () {
      const selectShowerTime = document.getElementById("shower-time");
      if (!this.checked) {
        selectShowerTime.selectedIndex = 0;
      } else {
        // 何もしない
      }
    });
  }
});

//料金計算
const feeArray = document.querySelectorAll(".fee");
// console.log(feeArray);
const total = document.getElementById("total-fee");
let totalFee = 0;
if (feeArray && total) {
  feeArray.forEach((eachFee) => {
    totalFee += parseInt(eachFee.dataset.fee);
  });
  total.innerHTML = totalFee.toLocaleString() + "円";
}

// ナビゲーションメニューの切替(確認画面から別画面移動時)
document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".with-confirm").forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const url = this.dataset.href;
      if (confirm("登録内容を破棄しますがよろしいですか？")) {
        window.location.href = BASE_URL + "/session_destroy?redirect=" + encodeURIComponent(url);
      }
    });
  });
});

// キャンセル前の確認
const cancelBtns = document.querySelectorAll('.form-btn.cancel');
if(cancelBtns.length > 0) {
  cancelBtns.forEach(btn => {
    btn.addEventListener('click', function () {
      const reserveId = this.dataset.reserveId;
      if(! confirm(`「予約ID: ${reserveId}」をキャンセルしますが宜しいですか？`)) {
        return;
      }
      this.closest('form').submit();
    })
  });
}


// ログアウト時の確認(ユーザー)
const logout = document.getElementById("logout");
if (logout) {
  logout.addEventListener("click", function () {
    if (confirm("ログアウトしますか？")) {
      document.getElementById("logout-form").submit();
    }
    return;
  });
}
// ログアウト時の確認(オーナー)
const ownerLogout = document.getElementById('owner-logout');
console.log(ownerLogout);
if(ownerLogout) {
  ownerLogout.addEventListener('click', e => {
    if(confirm('ログアウトしますか？')) {
      e.target.closest('form').submit();
    }
    return;
  })
}
