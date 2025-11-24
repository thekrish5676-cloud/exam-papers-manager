/* global jQuery */

// eslint-disable-next-line no-undef
const $ = jQuery

jQuery(document).ready(() => {
  // Declare variables before using them
  window.gtag =
    window.gtag ||
    (() => {}) // Placeholder for gtag // Placeholder for gtag
  window.epm_ajax = window.epm_ajax || {} // Placeholder for epm_ajax

  // Filter toggle functionality
  $(".epm-filter-title").on("click", function () {
    const content = $(this).next(".epm-filter-content")
    const toggle = $(this).find(".epm-toggle")

    if (content.hasClass("active")) {
      content.removeClass("active").slideUp(300)
      $(this).removeClass("active")
      toggle.text("+")
    } else {
      content.addClass("active").slideDown(300)
      $(this).addClass("active")
      toggle.text("−")
    }
  })

  // Checkbox functionality
  $(".epm-checkbox").on("change", function () {
    const label = $(this).closest(".epm-checkbox-label")

    if ($(this).is(":checked")) {
      label.addClass("checked")
    } else {
      label.removeClass("checked")
    }

    applyFiltersAuto()
  })

  // Apply filters
  $(".epm-apply-filters").on("click", () => {
    applyFiltersAuto()
  })

  // Clear all filters
  $(".epm-clear-filters").on("click", () => {
    // Uncheck all checkboxes
    $(".epm-checkbox").prop("checked", false).trigger("change")

    // Reset items per page
    $(".epm-items-per-page").val("10")

    // Apply empty filters (show all)
    $(".epm-apply-filters").trigger("click")
  })

  // Items per page change
  $(".epm-items-per-page").on("change", () => {
    $(".epm-apply-filters").trigger("click")
  })

  // Update results count
  function updateResultsCount() {
    const count = $("#exam-papers-results .epm-paper-result").length
    $("#results-count").text(count)
  }

  // Initialize pagination
  function initializePagination() {
    const papers = $("#exam-papers-results .epm-paper-result")
    const itemsPerPage = Number.parseInt($(".epm-items-per-page").val()) || 10
    const totalPages = Math.ceil(papers.length / itemsPerPage)

    // Hide all papers first
    papers.hide()

    // Show first page
    papers.slice(0, itemsPerPage).show()

    // Show/hide pagination based on whether it's needed
    if (totalPages <= 1) {
      $(".epm-pagination-top").hide()
    } else {
      $(".epm-pagination-top").show()
      updatePaginationNumbers(1, totalPages)
    }
  }

  // Update pagination numbers
  function updatePaginationNumbers(currentPage, totalPages) {
    const paginationContainer = $(".epm-pagination-top")
    paginationContainer.empty()

    // Show up to 5 page numbers
    const startPage = Math.max(1, currentPage - 2)
    const endPage = Math.min(totalPages, startPage + 4)

    for (let i = startPage; i <= endPage; i++) {
      const button = $('<button class="epm-pagination-btn epm-pagination-number">' + i + "</button>")
      if (i === currentPage) {
        button.addClass("active")
      }
      paginationContainer.append(button)
    }

    // Add next button if needed
    if (currentPage < totalPages) {
      const nextButton = $('<button class="epm-pagination-btn epm-pagination-next">></button>')
      paginationContainer.append(nextButton)
    }

    // Re-bind click events
    $(".epm-pagination-number").on("click", function () {
      const page = Number.parseInt($(this).text())
      navigateToPage(page)
    })

    $(".epm-pagination-next").on("click", () => {
      const currentPage = Number.parseInt($(".epm-pagination-number.active").text())
      if (currentPage < totalPages) {
        navigateToPage(currentPage + 1)
      }
    })
  }

  function navigateToPage(page) {
    const papers = $("#exam-papers-results .epm-paper-result")
    const itemsPerPage = Number.parseInt($(".epm-items-per-page").val()) || 10
    const totalPages = Math.ceil(papers.length / itemsPerPage)

    // Hide all papers
    papers.hide()

    // Show papers for current page
    const startIndex = (page - 1) * itemsPerPage
    const endIndex = startIndex + itemsPerPage
    papers.slice(startIndex, endIndex).show()

    // Update pagination numbers
    updatePaginationNumbers(page, totalPages)

    // Scroll to top of results
    $("#exam-papers-results")[0].scrollIntoView({ behavior: "smooth" })
  }

  function updatePaginationButtons(currentPage, totalPages) {
    // Update active page
    $(".epm-pagination-number").removeClass("active")
    $(".epm-pagination-number")
      .eq(currentPage - 1)
      .addClass("active")

    // Update prev/next buttons
    $(".epm-pagination-prev").prop("disabled", currentPage === 1)
    $(".epm-pagination-next").prop("disabled", currentPage === totalPages)
  }

  // Paper download tracking
  $(".epm-btn-download").on("click", function (e) {
    const paperTitle = $(this).closest(".epm-paper-result").find(".epm-paper-title").text()

    // Track download event
    if (typeof window.gtag !== "undefined") {
      window.gtag("event", "download", {
        event_category: "engagement",
        event_label: paperTitle,
      })
    }

    // Show download feedback
    const originalText = $(this).text()
    $(this).text("Downloading...").prop("disabled", true)

    setTimeout(() => {
      $(this).text(originalText).prop("disabled", false)
    }, 2000)
  })

  // Paper view tracking
  $(".epm-btn-view").on("click", function (e) {
    const paperTitle = $(this).closest(".epm-paper-result").find(".epm-paper-title").text()

    // Track view event
    if (typeof window.gtag !== "undefined") {
      window.gtag("event", "view_item", {
        event_category: "engagement",
        event_label: paperTitle,
      })
    }
  })

  // Search within results (client-side)
  // Enhanced Search Bar Implementation
  // Add this to your frontend.js file

  // Create enhanced search input with wrapper and icon
  const searchWrapper = $('<div class="epm-search-wrapper"></div>')
  const searchInput = $('<input type="text" class="epm-search-results" placeholder="Search resources">')
  const searchIcon = $('<span class="epm-search-icon">🔍</span>')

  // Append elements
  searchWrapper.append(searchInput)
  searchWrapper.append(searchIcon)

  // Insert into results header (between results count and controls)
  searchWrapper.insertAfter(".epm-results-count")

  // Search functionality with debouncing
  let searchTimeout
  searchInput.on("input", function () {
    clearTimeout(searchTimeout)
    const searchTerm = $(this).val().toLowerCase()

    // Add loading state
    searchWrapper.addClass("loading")

    searchTimeout = setTimeout(() => {
      const papers = $("#exam-papers-results .epm-paper-result")

      if (searchTerm === "") {
        papers.show()
      } else {
        papers.each(function () {
          const paperText = $(this).text().toLowerCase()
          if (paperText.includes(searchTerm)) {
            $(this).show()
          } else {
            $(this).hide()
          }
        })
      }

      // Update results count
      updateResultsCount()

      // Remove loading state
      searchWrapper.removeClass("loading")
    }, 300)
  })

  // Clear search on Escape key
  searchInput.on("keydown", function (e) {
    if (e.key === "Escape") {
      $(this).val("").trigger("input")
    }
  })

  // Show error messages
  function showError(message) {
    const errorDiv = $('<div class="epm-error-message">' + message + "</div>")
    errorDiv.insertBefore("#exam-papers-results").fadeIn().delay(5000).fadeOut()
  }

  // Responsive filter toggle for mobile
  if ($(window).width() <= 768) {
    const filterToggle = $('<button class="epm-mobile-filter-toggle">Show Filters</button>')
    filterToggle.insertBefore(".epm-sidebar")

    filterToggle.on("click", function () {
      $(".epm-sidebar").slideToggle()
      $(this).text($(this).text() === "Show Filters" ? "Hide Filters" : "Show Filters")
    })

    // Hide sidebar by default on mobile
    $(".epm-sidebar").hide()
  }

  // Keyboard shortcuts
  $(document).on("keydown", (e) => {
    // Ctrl+F for search
    if (e.ctrlKey && e.key === "f") {
      e.preventDefault()
      $(".epm-search-results").focus()
    }

    // Arrow keys for pagination
    if (e.key === "ArrowLeft") {
      $(".epm-pagination-prev").trigger("click")
    } else if (e.key === "ArrowRight") {
      $(".epm-pagination-next").trigger("click")
    }
  })

  // Smooth scrolling for anchor links
  $('a[href^="#"]').on("click", function (e) {
    e.preventDefault()
    const target = $($(this).attr("href"))
    if (target.length) {
      $("html, body").animate(
        {
          scrollTop: target.offset().top - 100,
        },
        500,
      )
    }
  })

  // Lazy loading for better performance
  function lazyLoadImages() {
    const images = $(".epm-paper-result img[data-src]")
    images.each(function () {
      if (isInViewport($(this))) {
        $(this).attr("src", $(this).data("src")).removeAttr("data-src")
      }
    })
  }

  function isInViewport($element) {
    const elementTop = $element.offset().top
    const elementBottom = elementTop + $element.outerHeight()
    const viewportTop = $(window).scrollTop()
    const viewportBottom = viewportTop + $(window).height()

    return elementBottom > viewportTop && elementTop < viewportBottom
  }

  $(window).on("scroll resize", lazyLoadImages)
  lazyLoadImages() // Initial load

  // Auto-save filter preferences
  $(".epm-checkbox").on("change", () => {
    const filterState = {}
    $(".epm-checkbox:checked").each(function () {
      const name = $(this).attr("name")
      const value = $(this).val()
      if (!filterState[name]) filterState[name] = []
      filterState[name].push(value)
    })

    localStorage.setItem("epm_filter_state", JSON.stringify(filterState))
  })

  // Restore filter preferences
  function restoreFilterState() {
    const savedState = localStorage.getItem("epm_filter_state")
    if (savedState) {
      const filterState = JSON.parse(savedState)

      Object.keys(filterState).forEach((name) => {
        filterState[name].forEach((value) => {
          $('input[name="' + name + '"][value="' + value + '"]')
            .prop("checked", true)
            .trigger("change")
        })
      })
    }
  }

  // Initialize
  restoreFilterState()
  updateResultsCount()
  initializePagination()

  // Initialize papers display on page load
  const papers = $("#exam-papers-results .epm-paper-result")
  if (papers.length > 0) {
    // Show first 10 papers by default
    const itemsPerPage = Number.parseInt($(".epm-items-per-page").val()) || 10
    papers.hide()
    papers.slice(0, itemsPerPage).show()
  }

  // Add loading animation
  $(document)
    .ajaxStart(() => {
      $(".epm-main-content").addClass("loading")
    })
    .ajaxStop(() => {
      $(".epm-main-content").removeClass("loading")
    })

  function applyFiltersAuto() {
    const button = $(".epm-apply-filters")

    // Show loading state
    button.text("Filtering...").prop("disabled", true)

    // Collect filter data
    const filters = {
      qualification: [],
      year_of_paper: [],
      resource_type: [],
    }

    // Get checked qualifications
    $('input[name="qualification"]:checked').each(function () {
      filters.qualification.push($(this).val())
    })

    // Get checked years
    $('input[name="year_of_paper"]:checked').each(function () {
      filters.year_of_paper.push($(this).val())
    })

    // Get checked resource types
    $('input[name="resource_type"]:checked').each(function () {
      filters.resource_type.push($(this).val())
    })

    // Get items per page
    const itemsPerPage = $(".epm-items-per-page").val() || 10

    // Debug logging
    console.log("Selected filters:", filters)

    const filterData = {
      action: "filter_exam_papers",
      qualification: filters.qualification.join(","),
      year_of_paper: filters.year_of_paper.join(","),
      resource_type: filters.resource_type.join(","),
      items_per_page: itemsPerPage,
      nonce: window.epm_ajax.nonce || "",
    }

    console.log("Sending filter data:", filterData)

    // Make AJAX request
    $.ajax({
      url: window.epm_ajax.ajax_url || "",
      type: "POST",
      data: filterData,
      success: (response) => {
        if (response.success) {
          $("#exam-papers-results").html(response.data)
          updateResultsCount()
          initializePagination()

          // Show first page of results
          const papers = $("#exam-papers-results .epm-paper-result")
          const itemsPerPage = Number.parseInt($(".epm-items-per-page").val()) || 10
          papers.hide()
          papers.slice(0, itemsPerPage).show()
        } else {
          showError("Failed to filter papers. Please try again.")
        }
      },
      error: () => {
        showError("Connection error. Please check your internet connection and try again.")
      },
      complete: () => {
        button.text("Apply Filters").prop("disabled", false)
      },
    })
  }
})
