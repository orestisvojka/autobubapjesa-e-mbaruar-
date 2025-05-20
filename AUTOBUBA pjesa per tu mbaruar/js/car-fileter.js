// Wait for the document to be fully loaded
document.addEventListener("DOMContentLoaded", () => {
  // Initialize price slider
  initPriceSlider()

  // Initialize search functionality
  initSearch()

  // Initialize brand filter checkboxes
  initBrandFilters()

  // Initialize show on page and sort by functionality
  initShowAndSort()

  // Add event listener to the Apply Filters button
  document.querySelector('.btn-danger[data-translate="Apply Filters"]').addEventListener("click", applyAllFilters)
})

// Initialize price slider functionality
function initPriceSlider() {
  const priceSlider = document.getElementById("priceSlider")
  const priceValue = document.getElementById("priceValue")

  if (priceSlider && priceValue) {
    // Format initial value
    priceValue.textContent = new Intl.NumberFormat().format(priceSlider.value)

    // Update on input
    priceSlider.addEventListener("input", function () {
      priceValue.textContent = new Intl.NumberFormat().format(this.value)
      updateSliderBackground(this)
    })

    // Set initial background
    updateSliderBackground(priceSlider)
  }
}

// Update slider background to show filled portion
function updateSliderBackground(slider) {
  const value = ((slider.value - slider.min) / (slider.max - slider.min)) * 100
  slider.style.background = `linear-gradient(to right, #dc3545 0%, #dc3545 ${value}%, #e9ecef ${value}%, #e9ecef 100%)`
}

// Initialize search functionality
function initSearch() {
  const searchInput = document.querySelector('.input-group input[type="text"]')

  if (searchInput) {
    searchInput.addEventListener("input", () => {
      // We'll apply the search when the Apply Filters button is clicked
      // But we could also add a debounce function here for real-time filtering
    })
  }
}

// Initialize brand filter checkboxes
function initBrandFilters() {
  const brandCheckboxes = document.querySelectorAll('.brand-filter input[type="checkbox"]')

  brandCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", () => {
      // We'll apply the brand filters when the Apply Filters button is clicked
    })
  })
}

// Initialize show on page and sort by functionality
function initShowAndSort() {
  const showOnPageSelect = document.getElementById("showOnPage")
  const sortBySelect = document.getElementById("sortBy")

  if (showOnPageSelect) {
    showOnPageSelect.addEventListener("change", () => {
      applyShowOnPage()
    })
  }

  if (sortBySelect) {
    sortBySelect.addEventListener("change", () => {
      applySortBy()
    })
  }

  // Apply default values
  applyShowOnPage()
  applySortBy()
}

// Apply all filters when the Apply Filters button is clicked
function applyAllFilters() {
  // Get filter values
  const searchTerm = document.querySelector('.input-group input[type="text"]').value.toLowerCase()
  const maxPrice = Number.parseInt(document.getElementById("priceSlider").value)
  const selectedBrands = getSelectedBrands()

  // Apply filters to each car item
  const carItems = document.querySelectorAll(".car-item")

  carItems.forEach((carItem) => {
    const carName = carItem.querySelector("h5 a").textContent.toLowerCase()
    const carPrice = Number.parseInt(carItem.getAttribute("data-price"))

    // Check if car matches search term
    const matchesSearch = searchTerm === "" || carName.includes(searchTerm)

    // Check if car price is within range
    const matchesPrice = carPrice <= maxPrice

    // Check if car matches selected brands
    let matchesBrand = true
    if (selectedBrands.length > 0) {
      matchesBrand = false
      for (const brand of selectedBrands) {
        if (carName.includes(brand.toLowerCase())) {
          matchesBrand = true
          break
        }
      }
    }

    // Mark items as filtered out or not
    if (matchesSearch && matchesPrice && matchesBrand) {
      carItem.dataset.filteredOut = "false"
      carItem.style.display = "" // Make visible initially
    } else {
      carItem.dataset.filteredOut = "true"
      carItem.style.display = "none"
    }
  })

  // Re-apply sorting and pagination after filtering
  applySortBy()
  applyShowOnPage()
}

// Get selected brands from checkboxes
function getSelectedBrands() {
  const selectedBrands = []
  const brandCheckboxes = document.querySelectorAll('.brand-filter input[type="checkbox"]:checked')

  brandCheckboxes.forEach((checkbox) => {
    const brandName = checkbox.nextElementSibling.textContent.trim()
    selectedBrands.push(brandName)
  })

  return selectedBrands
}

// Apply show on page functionality
function applyShowOnPage() {
  const showOnPageSelect = document.getElementById("showOnPage")
  if (!showOnPageSelect) return

  const carsPerPage = Number.parseInt(showOnPageSelect.value)

  // First, get all car items that should be visible based on other filters
  const allCarItems = document.querySelectorAll(".car-item")

  // Reset display for all cars that match the filters
  allCarItems.forEach((item) => {
    // If the item was hidden by filters, keep it hidden
    if (item.dataset.filteredOut === "true") {
      item.style.display = "none"
    } else {
      // Otherwise, make it visible (we'll limit the count below)
      item.style.display = ""
    }
  })

  // Now get the cars that are actually visible after filters
  const visibleCarItems = Array.from(allCarItems).filter((item) => item.dataset.filteredOut !== "true")

  // Hide cars beyond the selected number
  if (visibleCarItems.length > carsPerPage) {
    visibleCarItems.slice(carsPerPage).forEach((item) => {
      item.style.display = "none"
    })
  }

  // Update count display if you have one
  const visibleCount = Math.min(visibleCarItems.length, carsPerPage)
  console.log(`Showing ${visibleCount} of ${visibleCarItems.length} matching cars`)
}

// Apply sort by functionality
function applySortBy() {
  const sortBySelect = document.getElementById("sortBy")
  if (!sortBySelect) return

  const sortOption = sortBySelect.value
  const carGrid = document.getElementById("carGrid")

  // Get all visible car items
  const carItems = Array.from(document.querySelectorAll(".car-item")).filter(
    (item) => getComputedStyle(item).display !== "none",
  )

  // Sort cars based on selected option
  carItems.sort((a, b) => {
    const priceA = Number.parseInt(a.getAttribute("data-price"))
    const priceB = Number.parseInt(b.getAttribute("data-price"))
    const yearA = Number.parseInt(a.getAttribute("data-year"))
    const yearB = Number.parseInt(b.getAttribute("data-year"))

    switch (sortOption) {
      case "price-high":
        return priceB - priceA
      case "price-low":
        return priceA - priceB
      case "newest":
        return yearB - yearA
      case "popular":
        // For demo purposes, we'll just use a random sort for "popular"
        return 0.5 - Math.random()
      default:
        return 0
    }
  })

  // Reorder the car items in the DOM
  carItems.forEach((item) => {
    carGrid.appendChild(item)
  })
}
