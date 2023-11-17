 function updateCounter(textarea) {
  var messageLength = textarea.value.length;
    textarea.value = textarea.value.substr(0, 70); // Ограничение длины текста до 70 символов
    var counter = 70 - messageLength;
    document.getElementById('counter').textContent = counter;
  }
