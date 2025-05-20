document.addEventListener("DOMContentLoaded", () => {
    // Set background image for hero section
    const heroSection = document.querySelector(".hero")
    if (heroSection) {
      const bgImage = heroSection.getAttribute("data-setbg")
      if (bgImage) {
        heroSection.style.backgroundImage = `url(${bgImage})`
      }
    }
  
    // Initialize price range sliders
    initPriceRangeSliders()
  
    // Handle form submissions
    setupFormSubmissions()
  
    // Setup tab functionality if not already handled by Bootstrap
    setupTabs()
  })
  
  /**
   * Initialize price range sliders for both rental and buy forms
   */
  function initPriceRangeSliders() {
    // Check if jQuery UI is available
    if (typeof $.fn.slider !== "undefined") {
      // Rental form price slider
      $(".price-range").slider({
        range: true,
        min: 1,
        max: 600000,
        values: [80000, 320000],
        slide: (event, ui) => {
          $("#amount").val("$" + ui.values[0].toLocaleString() + " - $" + ui.values[1].toLocaleString())
        },
      })
      $("#amount").val(
        "$" +
          $(".price-range").slider("values", 0).toLocaleString() +
          " - $" +
          $(".price-range").slider("values", 1).toLocaleString(),
      )
  
      // Buy form price slider
      $(".buy-price-range").slider({
        range: true,
        min: 1000,
        max: 500000,
        values: [10000, 200000],
        slide: (event, ui) => {
          $("#buyAmount").val("$" + ui.values[0].toLocaleString() + " - $" + ui.values[1].toLocaleString())
        },
      })
      $("#buyAmount").val(
        "$" +
          $(".buy-price-range").slider("values", 0).toLocaleString() +
          " - $" +
          $(".buy-price-range").slider("values", 1).toLocaleString(),
      )
    } else {
      console.warn("jQuery UI slider not available. Price range sliders will not function.")
    }
  }
  
  /**
   * Setup form submissions for both rental and buy forms
   */
  function setupFormSubmissions() {
    // Sample car data (replace with your actual data or API call)
    const carData = [
      { brand: "Acura", model: "MDX", year: 2020, mileage: 27, price: 45000, type: "rent", image: "img/cars/car-1.jpg" },
      { brand: "Audi", model: "Q3", year: 2019, mileage: 25, price: 38000, type: "rent", image: "img/cars/car-2.jpg" },
      { brand: "BMW", model: "X5", year: 2018, mileage: 15, price: 42000, type: "buy", image: "img/cars/car-3.jpg" },
      {
        brand: "Bentley",
        model: "Continental",
        year: 2020,
        mileage: 10,
        price: 180000,
        type: "buy",
        image: "img/cars/car-4.jpg",
      },
      {
        brand: "Bugatti",
        model: "Chiron",
        year: 2019,
        mileage: 10,
        price: 2500000,
        type: "buy",
        image: "img/cars/car-5.jpg",
      },
      { brand: "Audi", model: "A4", year: 2017, mileage: 25, price: 32000, type: "rent", image: "img/cars/car-6.jpg" },
    ]
  
    // Rental form submission
    const rentalForm = document.getElementById("rentalForm")
    if (rentalForm) {
      rentalForm.addEventListener("submit", (e) => {
        e.preventDefault()
  
        const year = document.getElementById("rentYear").value
        const brand = document.getElementById("rentBrand").value
        const model = document.getElementById("rentModel").value
        const mileage = document.getElementById("rentMileage").value
        const priceRange = document.getElementById("amount").value
  
        const results = searchCars(carData, year, brand, model, mileage, priceRange, "rent")
        displaySearchResults(results)
      })
    }
  
    // Buy form submission
    const buyForm = document.getElementById("buyForm")
    if (buyForm) {
      buyForm.addEventListener("submit", (e) => {
        e.preventDefault()
  
        const year = document.getElementById("buyYear").value
        const brand = document.getElementById("buyBrand").value
        const model = document.getElementById("buyModel").value
        const mileage = document.getElementById("buyMileage").value
        const priceRange = document.getElementById("buyAmount").value
  
        const results = searchCars(carData, year, brand, model, mileage, priceRange, "buy")
        displaySearchResults(results)
      })
    }
  }
  
  /**
   * Search cars based on form criteria
   */
  function searchCars(cars, year, brand, model, mileage, priceRange, type) {
    // Parse price range
    let minPrice = 0
    let maxPrice = Number.MAX_SAFE_INTEGER
  
    if (priceRange) {
      const priceMatch = priceRange.match(/\$([0-9,]+) - \$([0-9,]+)/)
      if (priceMatch) {
        minPrice = Number.parseInt(priceMatch[1].replace(/,/g, ""))
        maxPrice = Number.parseInt(priceMatch[2].replace(/,/g, ""))
      }
    }
  
    // Filter cars based on criteria
    return cars.filter((car) => {
      if (type && car.type !== type) return false
      if (year && car.year.toString() !== year) return false
      if (brand && car.brand !== brand) return false
      if (model && car.model !== model) return false
      if (mileage && car.mileage.toString() !== mileage) return false
      if (car.price < minPrice || car.price > maxPrice) return false
  
      return true
    })
  }
  
  /**
   * Display search results in the modal
   */
  function displaySearchResults(results) {
    const searchModal = document.getElementById("searchModal")
    const searchResults = document.getElementById("searchResults")
  
    if (!searchModal || !searchResults) {
      console.error("Search modal or results container not found")
      return
    }
  
    // Clear previous results
    searchResults.innerHTML = ""
  
    if (results.length === 0) {
      searchResults.innerHTML = `
              <div class="no-results text-center py-4">
                  <p>No results found. Try different search criteria.</p>
              </div>
          `
    } else {
      results.forEach((car) => {
        const resultItem = document.createElement("div")
        resultItem.className = "car-result d-flex mb-3"
  
        resultItem.innerHTML = `
                  <img src="${car.image}" alt="${car.brand} ${car.model}" class="car-result-image">
                  <div class="car-result-details">
                      <h3>${car.brand} ${car.model}</h3>
                      <p><strong>Year:</strong> ${car.year}</p>
                      <p><strong>Mileage:</strong> ${car.mileage} mpg</p>
                      <p class="price"><strong>Price:</strong> $${car.price.toLocaleString()}</p>
                      <div class="car-result-actions">
                          <button class="add-to-cart-btn btn btn-sm btn-outline-danger">
                              <i class="fa fa-cart-plus"></i> Add to Cart
                          </button>
                          <button class="test-drive-btn btn btn-sm btn-outline-danger ml-2">
                              <i class="fa fa-car"></i> Test Drive
                          </button>
                      </div>
                  </div>
              `
  
        searchResults.appendChild(resultItem)
      })
    }
  
    // Show the modal using vanilla JavaScript instead of jQuery
    // This is more reliable if there are issues with the jQuery modal implementation
    searchModal.style.display = "block"
    document.body.classList.add("modal-open")
  
    // Create backdrop if it doesn't exist
    let backdrop = document.querySelector(".modal-backdrop")
    if (!backdrop) {
      backdrop = document.createElement("div")
      backdrop.className = "modal-backdrop fade show"
      document.body.appendChild(backdrop)
    }
  
    // Make sure the modal is visible and has the correct classes
    searchModal.classList.add("show")
    searchModal.setAttribute("aria-hidden", "false")
    searchModal.style.paddingRight = "17px" // Standard Bootstrap padding
  
    // Add event listeners to the close buttons
    const closeButtons = searchModal.querySelectorAll('[data-dismiss="modal"]')
    closeButtons.forEach((button) => {
      button.addEventListener("click", closeSearchModal)
    })
  
    // Add event listener to close modal when clicking outside
    searchModal.addEventListener("click", (event) => {
      if (event.target === searchModal) {
        closeSearchModal()
      }
    })
  
    // Add event listeners to the new buttons
    setupResultActions()
  }
  
  // Add a function to properly close the modal
  function closeSearchModal() {
    const searchModal = document.getElementById("searchModal")
    if (!searchModal) return
  
    searchModal.style.display = "none"
    searchModal.classList.remove("show")
    searchModal.setAttribute("aria-hidden", "true")
  
    // Remove backdrop
    const backdrop = document.querySelector(".modal-backdrop")
    if (backdrop) {
      backdrop.remove()
    }
  
    // Remove modal-open class from body
    document.body.classList.remove("modal-open")
    document.body.style.paddingRight = ""
  }
  
  /**
   * Setup actions for search result items (add to cart, test drive)
   */
  function setupResultActions() {
    // Add to cart functionality
    document.querySelectorAll(".add-to-cart-btn").forEach((btn) => {
      btn.addEventListener("click", function () {
        const carName = this.closest(".car-result").querySelector("h3").textContent
        addToCart(carName)
      })
    })
  
    // Test drive functionality
    document.querySelectorAll(".test-drive-btn").forEach((btn) => {
      btn.addEventListener("click", function () {
        const carName = this.closest(".car-result").querySelector("h3").textContent
        window.location.href = "Book a Test Drive.html?car=" + encodeURIComponent(carName)
      })
    })
  }
  
  /**
   * Add a car to the cart
   */
  function addToCart(carName) {
    // Get existing cart items from localStorage
    const cartItems = JSON.parse(localStorage.getItem("cartItems")) || []
  
    // Check if car is already in cart
    const existingItem = cartItems.find((item) => item.name === carName)
  
    if (!existingItem) {
      // Add new item to cart
      cartItems.push({
        id: Date.now(),
        name: carName,
        price: "Price on request", // You might want to pass the actual price
        image: "img/cars/car-1.jpg", // You might want to pass the actual image
      })
  
      // Save updated cart to localStorage
      localStorage.setItem("cartItems", JSON.stringify(cartItems))
  
      // Show notification
      showNotification("Car added to cart!")
  
      // Update cart counter
      updateCartCounter(cartItems.length)
    } else {
      showNotification("This car is already in your cart!")
    }
  }
  
  /**
   * Show a notification message
   */
  function showNotification(message) {
    const notification = document.createElement("div")
    notification.className = "notification alert alert-success"
    notification.textContent = message
    notification.style.cssText = `
          position: fixed;
          bottom: 20px;
          right: 20px;
          z-index: 9999;
          animation: fadeIn 0.3s;
      `
  
    document.body.appendChild(notification)
  
    setTimeout(() => {
      notification.style.animation = "fadeOut 0.3s"
      setTimeout(() => {
        notification.remove()
      }, 300)
    }, 3000)
  }
  
  /**
   * Update the cart counter display
   */
  function updateCartCounter(count) {
    const cartCounter = document.getElementById("cart-count")
    if (cartCounter) {
      cartCounter.textContent = count
      cartCounter.style.display = count > 0 ? "block" : "none"
    }
  }
  
  /**
   * Setup tab functionality if not already handled by Bootstrap
   */
  function setupTabs() {
    const tabLinks = document.querySelectorAll(".hero__tab .nav-tabs .nav-link")
    const tabContents = document.querySelectorAll(".hero__tab .tab-content .tab-pane")
  
    // If Bootstrap is handling tabs, this won't be necessary
    // This is a fallback in case Bootstrap JS is not loaded
    tabLinks.forEach((link) => {
      link.addEventListener("click", function (e) {
        e.preventDefault()
  
        // Remove active class from all tabs
        tabLinks.forEach((tab) => tab.classList.remove("active"))
        tabContents.forEach((content) => content.classList.remove("active"))
  
        // Add active class to clicked tab
        this.classList.add("active")
  
        // Get the target tab content
        const target = this.getAttribute("href")
        document.querySelector(target).classList.add("active")
      })
    })
  }
  
  // Fix for undeclared $ variable
  var $ = jQuery
  
  document.addEventListener("DOMContentLoaded", () => {
    // Add event listeners to close buttons in the modal
    const closeButtons = document.querySelectorAll('[data-dismiss="modal"]')
    closeButtons.forEach((button) => {
      button.addEventListener("click", closeSearchModal)
    })
  
    // Add ESC key listener to close modal
    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape" && document.getElementById("searchModal").classList.contains("show")) {
        closeSearchModal()
      }
    })
  })
  
  