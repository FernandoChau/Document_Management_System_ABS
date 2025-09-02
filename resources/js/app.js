import './bootstrap';

import "flyonui/flyonui.js"
import "lodash/lodash.js"
import "dropzone/dist/dropzone-min.js"

import "flatpickr/dist/flatpickr.min.css";
import "dropzone/dist/dropzone.css";

import flatpickr from "flatpickr";
import Dropzone from "dropzone";


Dropzone.autoDiscover = false;

// Init flatpickr
flatpickr(".datepicker", {
  mode: "range",
  static: true,
  monthSelectorType: "static",
  dateFormat: "M j, Y",
  defaultDate: [new Date().setDate(new Date().getDate() - 6), new Date()],
  prevArrow:
    '<svg class="stroke-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15.25 6L9 12.25L15.25 18.5" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  nextArrow:
    '<svg class="stroke-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.75 19L15 12.75L8.75 6.5" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  onReady: (selectedDates, dateStr, instance) => {
    // eslint-disable-next-line no-param-reassign
    instance.element.value = dateStr.replace("to", "-");
    const customClass = instance.element.getAttribute("data-class");
    instance.calendarContainer.classList.add(customClass);
  },
  onChange: (selectedDates, dateStr, instance) => {
    // eslint-disable-next-line no-param-reassign
    instance.element.value = dateStr.replace("to", "-");
  },
});


// Init Dropzone
const dropzoneArea = document.querySelectorAll("#demo-upload");

if (dropzoneArea.length) {
  const csrfToken = document.querySelector('input[name="_token"]').value;
  let myDropzone = new Dropzone("#demo-upload", {
    url: "/files",
    method: "post",
    headers: {
      'X-CSRF-TOKEN': csrfToken
    },
    paramName: "files[]", // nome do campo do arquivo para múltiplos
    uploadMultiple: true,
    addRemoveLinks: true,
    dictDefaultMessage: "Arraste e solte seus arquivos aqui ou clique para selecionar",
    autoProcessQueue: false, // O envio será manual
    parallelUploads: 10,
    init: function() {
      let self = this;
      document.getElementById("form-submit").addEventListener("click", function(e) {
        e.preventDefault();
        // Adiciona campos extras ao FormData
        self.processQueue();
      });
      self.on("sendingmultiple", function(files, xhr, formData) {
        // Adiciona campos extras ao FormData
        let nameInputs = document.querySelectorAll('input[name="names[]"]');
        nameInputs.forEach(input => {
          formData.append('names[]', input.value);
        });
        formData.append("folder_id", document.getElementById("folder-id").value);
        formData.append("tag", document.getElementById("file-tag").value);
      });
      self.on("successmultiple", function(files, response) {
        // Lógica após upload múltiplo
      });
      self.on("errormultiple", function(files, errorMessage) {
        // Lógica de erro múltiplo
      });
    }
  });
}

// Document Loaded
document.addEventListener("DOMContentLoaded", () => {
  chart01();
  chart02();
  chart03();
  map01();
});

// Get the current year
const year = document.getElementById("year");
if (year) {
  year.textContent = new Date().getFullYear();
}

// For Copy//
document.addEventListener("DOMContentLoaded", () => {
  const copyInput = document.getElementById("copy-input");
  if (copyInput) {
    // Select the copy button and input field
    const copyButton = document.getElementById("copy-button");
    const copyText = document.getElementById("copy-text");
    const websiteInput = document.getElementById("website-input");

    // Event listener for the copy button
    copyButton.addEventListener("click", () => {
      // Copy the input value to the clipboard
      navigator.clipboard.writeText(websiteInput.value).then(() => {
        // Change the text to "Copied"
        copyText.textContent = "Copiado";

        // Reset the text back to "Copy" after 2 seconds
        setTimeout(() => {
          copyText.textContent = "Copiar";
        }, 2000);
      });
    });
  }
});


document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("search-input");
  const searchButton = document.getElementById("search-button");

  // Function to focus the search input
  function focusSearchInput() {
    searchInput.focus();
  }

  // Add click event listener to the search button
  searchButton.addEventListener("click", focusSearchInput);

  // Add keyboard event listener for Cmd+K (Mac) or Ctrl+K (Windows/Linux)
  document.addEventListener("keydown", function (event) {
    if ((event.metaKey || event.ctrlKey) && event.key === "k") {
      event.preventDefault(); // Prevent the default browser behavior
      focusSearchInput();
    }
  });

  // Add keyboard event listener for "/" key
  document.addEventListener("keydown", function (event) {
    if (event.key === "/" && document.activeElement !== searchInput) {
      event.preventDefault(); // Prevent the "/" character from being typed
      focusSearchInput();
    }
  });
});