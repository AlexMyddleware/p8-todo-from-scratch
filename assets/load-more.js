
const cardItems = document.querySelectorAll('.card-item');
const loadMoreBtn = document.getElementById('load-more');
// const cardCount = document.getElementById('card-count');
// const cardTotal = document.getElementById('card-total');
const cardTotal = cardItems.length;
const batchSize = 10;


let currentBatch = batchSize;
hideCards(0, cardItems.length);
showCards(0, currentBatch);



function showCards(start, end) {
  for (let i = start; i < end; i++) {
    if (cardItems[i]) {
      cardItems[i].classList.remove('hidden');
    }
  }
}

function hideCards(start, end) {
  for (let i = start; i < end; i++) {
    if (cardItems[i]) {
      cardItems[i].classList.add('hidden');
    }
  }
}



function onLoadMore() {
  const start = currentBatch;
  const end = currentBatch + batchSize;
  showCards(start, end);
  currentBatch += batchSize;
  if (currentBatch >= cardTotal) {
    loadMoreBtn.classList.add('hidden');
  }
}


loadMoreBtn.addEventListener('click', onLoadMore);
