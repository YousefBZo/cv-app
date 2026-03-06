/**
 * Shared validation helpers that mirror backend rules.
 * Each validator returns an errors object: { fieldName: ['message'] }
 * Empty object = no errors.
 */

const today = () => new Date().toISOString().substring(0, 10)
const MIN_DATE = '1950-01-01'
const MAX_FUTURE_DATE = '2036-12-31'

function isValidDate(value) {
  if (!value) return false
  const d = new Date(value)
  return d instanceof Date && !isNaN(d) && /^\d{4}-\d{2}-\d{2}$/.test(value)
}

// ---------- PROFILE ----------
export function validateProfile(form) {
  const e = {}
  if (!form.headline || form.headline.trim().length < 2) e.headline = ['Headline is required (at least 2 characters).']
  else if (form.headline.trim().length > 255) e.headline = ['Headline must be at most 255 characters.']

  if (!form.summary || form.summary.trim().length < 10) e.summary = ['Summary is required (at least 10 characters).']
  else if (form.summary.trim().length > 2000) e.summary = ['Summary must be at most 2000 characters.']

  if (!form.location || form.location.trim().length < 2) e.location = ['Location is required (at least 2 characters).']
  else if (form.location.trim().length > 255) e.location = ['Location must be at most 255 characters.']

  return e
}

// ---------- EXPERIENCE ----------
export function validateExperience(form) {
  const e = {}
  if (!form.company || form.company.trim().length < 2) e.company = ['Company name is required (at least 2 characters).']
  else if (form.company.trim().length > 255) e.company = ['Company name must be at most 255 characters.']

  if (!form.position || form.position.trim().length < 2) e.position = ['Position is required (at least 2 characters).']
  else if (form.position.trim().length > 255) e.position = ['Position must be at most 255 characters.']

  if (!form.start_date) e.start_date = ['Start date is required.']
  else if (!isValidDate(form.start_date)) e.start_date = ['Start date must be a valid date (YYYY-MM-DD).']
  else if (form.start_date < MIN_DATE) e.start_date = ['Start date must be after January 1, 1950.']
  else if (form.start_date > today()) e.start_date = ['Start date cannot be in the future.']

  if (form.end_date) {
    if (!isValidDate(form.end_date)) e.end_date = ['End date must be a valid date (YYYY-MM-DD).']
    else if (form.start_date && form.end_date < form.start_date) e.end_date = ['End date must be after the start date.']
    else if (form.end_date > MAX_FUTURE_DATE) e.end_date = ['End date seems unrealistic.']
  }

  if (form.description && form.description.trim().length > 2000) e.description = ['Description must be at most 2000 characters.']

  return e
}

// ---------- EDUCATION ----------
export function validateEducation(form) {
  const e = {}
  if (!form.institution || form.institution.trim().length < 2) e.institution = ['Institution is required (at least 2 characters).']
  else if (form.institution.trim().length > 255) e.institution = ['Institution name must be at most 255 characters.']

  if (!form.degree || form.degree.trim().length < 2) e.degree = ['Degree is required (at least 2 characters).']
  else if (form.degree.trim().length > 255) e.degree = ['Degree must be at most 255 characters.']

  if (form.field_of_study && form.field_of_study.trim().length > 255) e.field_of_study = ['Field of study must be at most 255 characters.']

  if (!form.start_date) e.start_date = ['Start date is required.']
  else if (!isValidDate(form.start_date)) e.start_date = ['Start date must be a valid date (YYYY-MM-DD).']
  else if (form.start_date < MIN_DATE) e.start_date = ['Start date must be after January 1, 1950.']
  else if (form.start_date > today()) e.start_date = ['Start date cannot be in the future.']

  if (form.end_date) {
    if (!isValidDate(form.end_date)) e.end_date = ['End date must be a valid date (YYYY-MM-DD).']
    else if (form.start_date && form.end_date < form.start_date) e.end_date = ['End date must be after the start date.']
    else if (form.end_date > MAX_FUTURE_DATE) e.end_date = ['End date seems unrealistic.']
  }

  if (form.description && form.description.trim().length > 2000) e.description = ['Description must be at most 2000 characters.']

  return e
}

// ---------- PROJECT ----------
export function validateProject(form) {
  const e = {}
  if (!form.title || form.title.trim().length < 2) e.title = ['Project title is required (at least 2 characters).']
  else if (form.title.trim().length > 255) e.title = ['Project title must be at most 255 characters.']

  if (!form.description || form.description.trim().length < 10) e.description = ['Description is required (at least 10 characters).']
  else if (form.description.trim().length > 2000) e.description = ['Description must be at most 2000 characters.']

  if (form.link) {
    if (!/^https?:\/\/.+/.test(form.link)) e.link = ['Live link must be a valid URL (start with http:// or https://).']
    else if (form.link.length > 500) e.link = ['Live link must be at most 500 characters.']
  }

  if (form.github_url) {
    if (!/^https?:\/\/.+/.test(form.github_url)) e.github_url = ['GitHub URL must be a valid URL (start with http:// or https://).']
    else if (form.github_url.length > 500) e.github_url = ['GitHub URL must be at most 500 characters.']
  }

  if (form.start_date) {
    if (!isValidDate(form.start_date)) e.start_date = ['Start date must be a valid date (YYYY-MM-DD).']
    else if (form.start_date < MIN_DATE) e.start_date = ['Start date must be after January 1, 1950.']
    else if (form.start_date > today()) e.start_date = ['Start date cannot be in the future.']
  }

  if (form.end_date) {
    if (!isValidDate(form.end_date)) e.end_date = ['End date must be a valid date (YYYY-MM-DD).']
    else if (form.start_date && form.end_date < form.start_date) e.end_date = ['End date must be after the start date.']
    else if (form.end_date > MAX_FUTURE_DATE) e.end_date = ['End date seems unrealistic.']
  }

  return e
}

// ---------- CERTIFICATION ----------
export function validateCertification(form) {
  const e = {}
  if (!form.name || form.name.trim().length < 2) e.name = ['Certificate name is required (at least 2 characters).']
  else if (form.name.trim().length > 255) e.name = ['Certificate name must be at most 255 characters.']

  if (!form.organization || form.organization.trim().length < 2) e.organization = ['Organization is required (at least 2 characters).']
  else if (form.organization.trim().length > 255) e.organization = ['Organization name must be at most 255 characters.']

  if (!form.issue_date) e.issue_date = ['Issue date is required.']
  else if (!isValidDate(form.issue_date)) e.issue_date = ['Issue date must be a valid date (YYYY-MM-DD).']
  else if (form.issue_date < MIN_DATE) e.issue_date = ['Issue date must be after January 1, 1950.']
  else if (form.issue_date > today()) e.issue_date = ['Issue date cannot be in the future.']

  if (form.expiration_date) {
    if (!isValidDate(form.expiration_date)) e.expiration_date = ['Expiration date must be a valid date (YYYY-MM-DD).']
    else if (form.issue_date && form.expiration_date < form.issue_date) e.expiration_date = ['Expiration date must be after the issue date.']
  }

  return e
}

// ---------- VOLUNTEER ----------
export function validateVolunteer(form) {
  const e = {}
  if (!form.organization || form.organization.trim().length < 2) e.organization = ['Organization is required (at least 2 characters).']
  else if (form.organization.trim().length > 255) e.organization = ['Organization name must be at most 255 characters.']

  if (!form.role || form.role.trim().length < 2) e.role = ['Role is required (at least 2 characters).']
  else if (form.role.trim().length > 255) e.role = ['Role must be at most 255 characters.']

  if (!form.start_date) e.start_date = ['Start date is required.']
  else if (!isValidDate(form.start_date)) e.start_date = ['Start date must be a valid date (YYYY-MM-DD).']
  else if (form.start_date < MIN_DATE) e.start_date = ['Start date must be after January 1, 1950.']
  else if (form.start_date > today()) e.start_date = ['Start date cannot be in the future.']

  if (form.end_date) {
    if (!isValidDate(form.end_date)) e.end_date = ['End date must be a valid date (YYYY-MM-DD).']
    else if (form.start_date && form.end_date < form.start_date) e.end_date = ['End date must be after the start date.']
    else if (form.end_date > MAX_FUTURE_DATE) e.end_date = ['End date seems unrealistic.']
  }

  if (form.description && form.description.trim().length > 2000) e.description = ['Description must be at most 2000 characters.']

  return e
}

/**
 * Helper: returns true if errors object has any keys.
 */
export function hasErrors(errors) {
  return Object.keys(errors).length > 0
}
