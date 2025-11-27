// 削除ボタン
const deleteBtns = document.querySelectorAll('.del-btn');
if(deleteBtns.length > 0) {
  deleteBtns.forEach((btn) => {
    btn.addEventListener('click', function() {
      if(confirm('選択したデータを削除しても宜しいですか?')) {
        this.closest('form').submit();
      }
      return;
    })
  })
}
// ログアウト
const logoutBtn = document.getElementById('logout-box');
if(logoutBtn) {
  logoutBtn.addEventListener('click', function() {
    if(confirm('ログアウトしますか？')) {
      this.closest('form').submit();
    }
  })
}
// プレビュー画像
const image = document.getElementById('image');
if(image) {
  image.addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('preview');

    if(! file.type.match('image.*')) {
      alert('画像ファイルを選択してください');
      return;
    }
    
    if(file && file.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = function(event) {
        preview.src = event.target.result;
        preview.style.display = 'inline-block';
      };
      reader.readAsDataURL(file);
    } else {
      preview.src = '#';
      preview.style.display = 'none';
    }
  });
}

// プレビュー画像
const inputImage = document.querySelectorAll('.input-image');
if(inputImage.length > 0) {
  inputImage.forEach((input) => {
    input.addEventListener('change', function(e) {
      const file = e.target.files[0];
      const preview = document.getElementById(e.target.dataset.preview);
      
      if(file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(event) {
          preview.src = event.target.result;
          preview.style.display = 'inline-block';
        };
        reader.readAsDataURL(file);
      } else {
        preview.src = '#';
        preview.style.display = 'none';
      }
    });
  })
}

// 一時保存画像の削除
const tempDeleteBtns = document.querySelectorAll('.tmp-delete-btn');
if(tempDeleteBtns.length > 0) {
  tempDeleteBtns.forEach((button) => {
    button.addEventListener('click', function() {
      const wrapper = this.closest('.img-preview');
      const filename = wrapper.dataset.filename;
      const csrfToken = window.CSRF_TOKEN;
  
      fetch(window.BASE_PATH + '/owner/shop/deleteTempImage', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
          filename: filename,
          token: csrfToken
        }),
      })
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.json(); // JSON以外が混入していたらここでエラー
      })
      .then(data => {
        if (data.success) {
          const preview = document.querySelector(`.img-preview[data-filename="${filename}"]`);
          if (preview) {
            preview.remove(); // ← DOMから要素を削除
          }
        } else {
          alert(data.message || '削除に失敗しました');
        }
      })
      .catch(error => {
        console.error('Fetch error:', error);
        alert('通信エラーが発生しました');
      });

    });
  });
}

// 商品画像の削除（データベース/アップロードフォルダ）
const deleteImgBtns = document.querySelectorAll('.delete-img-btn');
if(deleteImgBtns.length > 0) {
  deleteImgBtns.forEach((button) => {
    button.addEventListener('click', function() {
      if(confirm("画像を削除しますか？（この時点で画像は削除されます）")) {
        const wrapper = this.closest('.grid-img');
        const img = wrapper.querySelector('.selected-img');
        const hiddenInputId = wrapper.querySelector('input[name="image_id[]"]')
        const filename = wrapper.querySelector('.current-img-wrapper').dataset.filename;
        const csrfToken = window.CSRF_TOKEN;
        
        fetch(window.BASE_PATH + '/owner/item/image/delete', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: JSON.stringify({
            filename: filename,
            token: csrfToken,
            image_id: hiddenInputId.value
          })
        })
        .then(response => {
          if(!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(data => {
          if(data.success) {
            const preview = document.querySelector(`.current-img-wrapper[data-filename="${filename}"]`);

            if (preview) {
              const img = preview.querySelector('img');
              if(img) {
                img.src = 'images/dummy.png';
                hiddenInputId.value = '';
                const tmpInput = wrapper.querySelector('input[name="tmp_image[]"]');
                if (tmpInput) tmpInput.value = '';
                const sortInput = wrapper.querySelector('input[name="sort_order[]"]');
                if (sortInput) sortInput.value = '';
              } else {
                alert(data.message || '画像が見つかりません');
              }
            }
          } else {
            alert(data.message || '削除に失敗しました');
          }
        })
        .catch(error => {
          console.error('Fetch error: ', error);
          alert('通信エラーが発生しました');
        })
      }
    })
  })
}
// 商品画像の更新処理時のドラッグ＆ドロップとsort_orderの番号振り
function updateSortOrderInputs() {
  const items = document.querySelectorAll('#sortable-list .sortable-item');
  let order = 1;
  items.forEach(item => {
    const sortInput = item.querySelector('input[name="sort_order[]"]');
    const imageInput = item.querySelector('input[type="file"]');
    const imageId = item.dataset.id;
    const tmpInput = item.querySelector('input[name="tmp_image[]"]');

    const hasImageId = imageId && imageId.trim() !== '';
    const hasTmp = tmpInput && tmpInput.value.trim() !== '';
    const hasFile = imageInput && imageInput.files.length > 0;

    if ((hasImageId || hasTmp || hasFile) && sortInput) {
      sortInput.value = order++;
    } else if (sortInput) {
      sortInput.value = '';
    }
  })
}

const sortableList = document.getElementById('sortable-list');
if(sortableList) {
  const sortable = Sortable.create(sortableList, {
    animation: 200,
    onEnd: updateSortOrderInputs
  });

  document.querySelectorAll('.input-image').forEach(input => {
    input.addEventListener('change', updateSortOrderInputs);
  });
  
  window.addEventListener('DOMContentLoaded', updateSortOrderInputs);
}

// 並べ替え
const itemSort = document.querySelector('#item-select');
if(itemSort) {
  itemSort.addEventListener('change', function() {
    document.querySelector('#sort-form input[name="page"]').value = 1;
    document.getElementById('sort-form').submit();
  })
}

// 1頁の表示数変更
const perPage = document.querySelector('#per-page');
if(perPage) {
  perPage.addEventListener('change', function() {
    document.getElementById('per-page-form').submit();
  })
}

// カテゴリー絞り込み
const categoryForm = document.getElementById('category-form');
if(categoryForm) {
  categoryForm.addEventListener('submit', function (e) {
    e.preventDefault();
    const parent = document.querySelector("[name='parent']").value;
    const sub = document.querySelector("[name='sub']").value;
    const item = document.querySelector("[name='item']").value;
    const perPage = document.querySelector("[name='per_page']").value;
    const itemSelect = document.querySelector("[name='item_select']").value;
    const itemSearch = document.querySelector("[name='item_search']").value;
    const page = document.querySelector("[name='page']").value;

    let url = '/ecsite/';
    if(parent) url += 'category/' + parent;
    if(sub) url += '/' + sub;
    if(item) url += '/' + item;

    const param = new URLSearchParams();
    if (perPage) param.append('per_page', perPage);
    if (itemSelect) param.append('item_select', itemSelect);
    if (itemSearch) param.append('item_search', itemSearch);
    if (page) param.append('page', page);

    const finalUrl = param.toString() ? `${url}?${param.toString()}` : url;
    location.href = finalUrl;
  });
}

/**
 * 3カテゴリーのoptionタグの生成
 */
const parentSelect = document.getElementById('parent');
const subSelect = document.getElementById('sub');
const itemSelect = document.getElementById('item');

// 初期化関数
function resetSelect(select, placeholder) {
  select.innerHTML = `<option value="">${placeholder}</option>`;
  select.disabled = true;
}

if (parentSelect && subSelect && itemSelect) {
  parentSelect.addEventListener('change', () => {
    const parentSlug = parentSelect.value;
    resetSelect(subSelect, '全てのサブカテゴリ');
    resetSelect(itemSelect, '全ての商品カテゴリ');

    const parent = categoryMap.find(cat => cat.slug === parentSlug);
    if (!parent) return;

    parent.children.forEach(sub => {
      const option = document.createElement('option');
      option.value = sub.slug;
      option.textContent = sub.name;
      subSelect.appendChild(option);
    });
    subSelect.disabled = false;
  });

  subSelect.addEventListener('change', () => {
    const parentSlug = parentSelect.value;
    const subSlug = subSelect.value;
    resetSelect(itemSelect, '商品カテゴリを選択');

    const parent = categoryMap.find(cat => cat.slug === parentSlug);
    const sub = parent?.children.find(s => s.slug === subSlug);
    if (!sub) return;

    sub.children.forEach(item => {
      const option = document.createElement('option');
      option.value = item.slug;
      option.textContent = item.name;
      itemSelect.appendChild(option);
    });
    itemSelect.disabled = false;
  });

  const pathParts = window.location.pathname.split('/');
  const selectedParent = pathParts[3] || null;
  const selectedSub = pathParts[4] || null;
  const selectedItem = pathParts[5] || null;

  if(selectedParent) {
    parentSelect.value = selectedParent
    parentSelect.dispatchEvent(new Event('change'));

    setTimeout(() => {
      if(selectedSub) {
        subSelect.value = selectedSub;
        subSelect.dispatchEvent(new Event('change'));
      }
    }, 100);

    setTimeout(() => {
      if(selectedItem) {
        itemSelect.value = selectedItem;
        itemSelect.dispatchEvent(new Event('change'));
      }
    }, 200)
  }
}

if(document.querySelectorAll('.cart-info').length > 0) {
  // cart：商品小計計算
  function updateSubtotals() {
    document.querySelectorAll('.cart-info').forEach(item => {
      const unitPrice = Number(item.querySelector('.unit-price').textContent.replace(/,/g, ''));
      const quantity = Number(item.querySelector('input[name="quantity"]').value);
      if(quantity < 1) return;
      const subtotal = unitPrice * quantity;
      item.querySelector('.subtotal-calc').textContent = subtotal.toLocaleString();
    });
  }
  // cart：合計金額計算
  function updateTotal() {
    let total = 0;
    document.querySelectorAll('.subtotal-calc').forEach(sub => {
      total += Number(sub.textContent.replace(/,/g, ""));
    });
    document.getElementById('total-price').textContent = total.toLocaleString();
  }
  
  document.querySelectorAll('input[name="quantity"]').forEach(input => {
    input.addEventListener('change', () => {
      updateSubtotals();
      updateTotal();
    });
  });
  updateSubtotals();
  updateTotal();
}

// カートへ戻すボタン（DOMから消去）
const cartBackBtns = document.querySelectorAll('.cart-back-btn')
if(cartBackBtns.length > 0) {
  cartBackBtns.forEach((btn) => {
    btn.addEventListener('click', function() {
      const btnId = this.dataset.id;
      const itemDiv = document.getElementById('data_' + btnId);
      const inputHidden = document.getElementById('order-cart_' + btnId);
      if(itemDiv) itemDiv.remove();
      if(inputHidden) inputHidden.remove();
      updateOrderTotal();
    })
  })
}

function updateOrderTotal() {
  const orderAmount = document.querySelectorAll('.amount');
  let total = 0;
  orderAmount.forEach(amount => {
    const amountValue = amount.textContent.replace(' +tax', '').replace(/,/g, "");
    total += Number(amountValue);
  });
  document.getElementById('order-total-amount').textContent = total.toLocaleString();

  const orderBtn = document.querySelector('.btn-primary');
  if (orderAmount.length === 0 && orderBtn) {
    // orderBtn.disabled = true;
    orderBtn.textContent = '商品が選択されていません';
  }
}
///////////////////////////////////////////

// カート数量変更(ajax）
const inputs = document.querySelectorAll('.quantity-input');
if(inputs.length > 0) {
  
  let debounceTimer = null;
  const confirmedQtyMap = {};

  inputs.forEach(input => {
    const cartId = input.dataset.cartId;
    const init = Math.max(1, parseInt(input.value, 10) || 1);
    input.value = init;
    confirmedQtyMap[cartId] = init;

    input.addEventListener('change', () => {
      if (debounceTimer) clearTimeout(debounceTimer);

      debounceTimer = setTimeout(() => handleChange(input), 800);
    });
  });

  async function handleChange(input) {
    const cartId = input.dataset.cartId;
    const csrfToken = window.CSRF_TOKEN;
    const qty = parseInt(input.value, 10) || 0;

    if(qty < 1) {
      alert('数量は1以上にしてください');
      input.value = confirmedQtyMap[cartId] ?? 1;
      return;
    }

    input.disabled = true;

    try {
      const res = await fetch('/ecsite/cart/update', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ cart_id: cartId, quantity: qty, token: csrfToken }),
      });

      if (res.status === 401) {
        alert('ログインセッションが切れています');
        location.href = '/ecsite/login';
        return;
      }

      if (res.status === 400) {
        location.href = '/ecsite/error?error_mode=400';
        return;
      }

      const isJson = (res.headers.get('content-type') || '').includes('application/json');
      const data = isJson ? await res.json().catch(() => null) : null;
      console.log('レスポンス内容:', data);

      if (!res.ok) {
        alert(data?.message || 'エラーが発生しました');
        input.value = confirmedQtyMap[cartId] ?? 1;
        return;
      }

      if (data?.success) {
        confirmedQtyMap[cartId] = qty;
        alert('数量を更新しました');
      } else {
        alert(data?.message || '更新に失敗しました');
        input.value = confirmedQtyMap[cartId] ?? 1;
      }
    } catch (e) {
      alert('通信エラーが発生しました');
      input.value = confirmedQtyMap[cartId] ?? 1;
    } finally {
      input.disabled = false;
      updateSubtotals();
      updateTotal();
    }
  }
}

// お気に入り登録
const favoriteBtn = document.getElementById('favorite-button');
if(favoriteBtn) {
  const isLoggedIn = favoriteBtn.dataset.isLoggedIn === 'true';
  const isFavorite = favoriteBtn.dataset.isFavorite === 'true';
  
  if(isFavorite) {
    favoriteBtn.classList.add('favorited');
  }
  
  favoriteBtn.addEventListener('click', function() {
    if(! isLoggedIn) {
      const url = new URL(window.location.href);
      const back = url.pathname + url.search;
      const currentUrl= new URLSearchParams({ login_backUrl: back });
      location.href = `/ecsite/login?${currentUrl.toString()}`;
      return;
    }
    
    const itemId = this.dataset.itemId;
    const csrfToken = window.CSRF_TOKEN;

    fetch('/ecsite/favorite/toggle', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        'item_id': itemId
      })
    })
    .then(res => res.json())
    .then(data => {
      if(data.success) {
        if(data.isFavorite) {
          favoriteBtn.classList.add('favorited');
        } else {
          favoriteBtn.classList.remove('favorited');
        }
      } else {
        alert(data.message || 'お気に入り処理に失敗しました');
      }
    });
  });
}
// お気に入り削除
const favoriteDelBtns = document.querySelectorAll('.favorite-del-btn');
if(favoriteDelBtns.length > 0) {
  favoriteDelBtns.forEach((deleteBtn) => {
    deleteBtn.addEventListener('click', function() {
      const wrapper = this.closest('.favorite');
      const favoriteId = wrapper.querySelector("input[name='favorite_id']").value;
      
      fetch('/ecsite/favorite/delete', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': window.CSRF_TOKEN,
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          'favorite_id': favoriteId,
        })
      })
      .then(res => res.json())
      .then(data => {
        if(data.success) {
          wrapper.remove();
        } else {
          alert(data.message || 'お気に入り削除に失敗しました');
        }
      })
    })
  })
}
//注文確認からお気に入りへ戻す処理
const moveFavoriteBtns = document.querySelectorAll('.move-favorite-btn');
if(moveFavoriteBtns.length > 0) {
  moveFavoriteBtns.forEach((moveBtn) => {
    moveBtn.addEventListener('click', function() {
      if(confirm('カートから削除し、お気に入りに追加しますが宜しいですか？')) {
        const cartId = this.dataset.cartId;
        const moveElement = this.closest('.confirm-item');
        
        fetch('/ecsite/favorite/move', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.CSRF_TOKEN,
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            'cart_id': cartId
          })
        })
        .then(res => res.json())
        .then(data => {
          if(data.success) {
            moveElement.remove();
            updateOrderTotal();
          } else {
            alert(data.message  || 'お気に入りへ移動できませんでした、再度お試しください');
          }
        })
      } else {
        return;
      }
    })
  })
}
// swiper
document.addEventListener('DOMContentLoaded', function() {
  const mainSwiperEl = document.querySelector('.main-swiper');
  const thumbSwiperEl = document.querySelector('.thumb-swiper');

  if (mainSwiperEl && thumbSwiperEl) {
    const thumbSwiper = new Swiper('.thumb-swiper', {
      spaceBetween: 10,
      slidesPerView: 4,
      watchSlidesProgress: true,
      breakpoints: {
        768: { slidesPerView: 5 },
        1024: { slidesPerView: 6 }
      }
    });

    const mainSwiper = new Swiper('.main-swiper', {
      spaceBetween: 10,
      effect: 'fade', // ← フェード切替
      fadeEffect: {
        crossFade: true,
      },
      zoom: true, // ← ズーム有効
      thumbs: {
        swiper: thumbSwiper,
      }
    });
  }
});

// confirm画面から遷移時の登録情報破棄
const confirmLinks = document.querySelectorAll('.with-confirm');
if(confirmLinks.length > 0) {
  document.addEventListener('click', (e) => {
    const a = e.target.closest('a[data-confirm]');
    if (!a) return;
    
    if (e.metaKey || e.ctrlKey || e.shiftKey || e.button === 1) return;
    
    e.preventDefault();
    if(! confirm('登録内容を破棄しても宜しいでしょうか？')) return;
  
    const form = document.getElementById('leave-confirm-form');
    form.elements.redirect.value = a.pathname;
    form.submit();
  })
}

