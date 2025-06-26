
document.addEventListener('DOMContentLoaded', () => {
  // ===== 成績の合計点・平均点を計算 =====
  const rows = document.querySelectorAll('.test-results tbody tr');

  rows.forEach(row => {
    const scoreInputs = row.querySelectorAll('td input[type="text"]');
    const totalCell = row.querySelector('.total');
    const avgCell = row.querySelector('.average');

    // 各教科の input にイベントリスナーを追加
    scoreInputs.forEach(input => {
      input.addEventListener('input', () => {
        let total = 0;
        let count = 0;

        scoreInputs.forEach(score => {
          const val = parseInt(score.value);
          if (!isNaN(val)) {
            total += val;
            count++;
          }
        });

        totalCell.textContent = total;
        avgCell.textContent = count ? Math.round(total / count) : 0;
      });
    });

    // 初期値でも計算して反映（ページ読み込み時）
    let total = 0;
    let count = 0;
    scoreInputs.forEach(score => {
      const val = parseInt(score.value);
      if (!isNaN(val)) {
        total += val;
        count++;
      }
    });
    totalCell.textContent = total;
    avgCell.textContent = count ? Math.round(total / count) : 0;
  });
});


// 成績削除ボタンが存在する場合のみ
document.addEventListener('DOMContentLoaded', function () {
  const deleteScoresBtn = document.querySelector('button[name="deleteScores"]');
  if (deleteScoresBtn) {
    deleteScoresBtn.addEventListener('click', function (e) {
      const confirmed = confirm('選択したテストの成績を本当に削除しますか？');
      if (!confirmed) {
        e.preventDefault(); // キャンセルならフォーム送信ストップ
      }
    });
  }
});



  // ===== 全削除ボタンの処理 =====
  const deleteBtn = document.querySelector('.delete-btn');
  if (deleteBtn) {
    deleteBtn.addEventListener('click', function (e) {
      const confirmed = confirm('本当に削除しますか？');
      if (!confirmed) {
        e.preventDefault();  // キャンセルされたら送信を止める
      }
    });
  }

  //行クリックで生徒編集ページに遷移
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.clickable-row').forEach(function(row) {
      row.addEventListener('click', function() {
        const id = this.dataset.id;
        window.location.href = '/student-data.php?id=' + id;
      });
    });
  });


// ===== 写真アップロードのプレビュー表示 =====
  document.getElementById('photo-upload').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file && file.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = function(e) {
        document.getElementById('preview').src = e.target.result;
      };
      reader.readAsDataURL(file);
    }
  });