/**
 * Shared validation helpers that mirror backend rules.
 * Each validator returns an errors object: { fieldName: ['message'] }
 * Empty object = no errors.
 */
import i18n from '@/i18n'

const t = (key, params) => i18n.global.t(key, params)

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
  if (!form.headline || form.headline.trim().length < 2) e.headline = [t('validation.headlineRequired')]
  else if (form.headline.trim().length > 255) e.headline = [t('validation.headlineMax')]

  if (!form.summary || form.summary.trim().length < 10) e.summary = [t('validation.summaryRequired')]
  else if (form.summary.trim().length > 2000) e.summary = [t('validation.summaryMax')]

  if (!form.location || form.location.trim().length < 2) e.location = [t('validation.locationRequired')]
  else if (form.location.trim().length > 255) e.location = [t('validation.locationMax')]

  // Optional contact fields
  if (form.phone && form.phone.trim().length > 30) e.phone = [t('validation.phoneMax')]

  if (form.contact_email && form.contact_email.trim()) {
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.contact_email.trim())) e.contact_email = [t('validation.contactEmailInvalid')]
    else if (form.contact_email.trim().length > 255) e.contact_email = [t('validation.contactEmailMax')]
  }

  const urlRegex = /^https?:\/\/.+/
  if (form.website && form.website.trim()) {
    if (!urlRegex.test(form.website.trim())) e.website = [t('validation.websiteInvalid')]
    else if (form.website.trim().length > 500) e.website = [t('validation.websiteMax')]
  }
  if (form.linkedin && form.linkedin.trim()) {
    if (!urlRegex.test(form.linkedin.trim())) e.linkedin = [t('validation.linkedinInvalid')]
    else if (form.linkedin.trim().length > 500) e.linkedin = [t('validation.linkedinMax')]
  }
  if (form.github && form.github.trim()) {
    if (!urlRegex.test(form.github.trim())) e.github = [t('validation.githubProfileInvalid')]
    else if (form.github.trim().length > 500) e.github = [t('validation.githubProfileMax')]
  }

  return e
}

// ---------- EXPERIENCE ----------
export function validateExperience(form) {
  const e = {}
  if (!form.company || form.company.trim().length < 2) e.company = [t('validation.companyRequired')]
  else if (form.company.trim().length > 255) e.company = [t('validation.companyMax')]

  if (!form.position || form.position.trim().length < 2) e.position = [t('validation.positionRequired')]
  else if (form.position.trim().length > 255) e.position = [t('validation.positionMax')]

  if (!form.start_date) e.start_date = [t('validation.startDateRequired')]
  else if (!isValidDate(form.start_date)) e.start_date = [t('validation.startDateInvalid')]
  else if (form.start_date < MIN_DATE) e.start_date = [t('validation.startDateMin')]
  else if (form.start_date > today()) e.start_date = [t('validation.startDateFuture')]

  if (form.end_date) {
    if (!isValidDate(form.end_date)) e.end_date = [t('validation.endDateInvalid')]
    else if (form.start_date && form.end_date < form.start_date) e.end_date = [t('validation.endDateBeforeStart')]
    else if (form.end_date > MAX_FUTURE_DATE) e.end_date = [t('validation.endDateUnrealistic')]
  }

  if (form.description && form.description.trim().length > 2000) e.description = [t('validation.descriptionMax')]

  return e
}

// ---------- EDUCATION ----------
export function validateEducation(form) {
  const e = {}
  if (!form.institution || form.institution.trim().length < 2) e.institution = [t('validation.institutionRequired')]
  else if (form.institution.trim().length > 255) e.institution = [t('validation.institutionMax')]

  if (!form.degree || form.degree.trim().length < 2) e.degree = [t('validation.degreeRequired')]
  else if (form.degree.trim().length > 255) e.degree = [t('validation.degreeMax')]

  if (form.field_of_study && form.field_of_study.trim().length > 255) e.field_of_study = [t('validation.fieldOfStudyMax')]

  if (!form.start_date) e.start_date = [t('validation.startDateRequired')]
  else if (!isValidDate(form.start_date)) e.start_date = [t('validation.startDateInvalid')]
  else if (form.start_date < MIN_DATE) e.start_date = [t('validation.startDateMin')]
  else if (form.start_date > today()) e.start_date = [t('validation.startDateFuture')]

  if (form.end_date) {
    if (!isValidDate(form.end_date)) e.end_date = [t('validation.endDateInvalid')]
    else if (form.start_date && form.end_date < form.start_date) e.end_date = [t('validation.endDateBeforeStart')]
    else if (form.end_date > MAX_FUTURE_DATE) e.end_date = [t('validation.endDateUnrealistic')]
  }

  if (form.description && form.description.trim().length > 2000) e.description = [t('validation.descriptionMax')]

  return e
}

// ---------- PROJECT ----------
export function validateProject(form) {
  const e = {}
  if (!form.title || form.title.trim().length < 2) e.title = [t('validation.titleRequired')]
  else if (form.title.trim().length > 255) e.title = [t('validation.titleMax')]

  if (!form.description || form.description.trim().length < 10) e.description = [t('validation.descriptionRequired')]
  else if (form.description.trim().length > 2000) e.description = [t('validation.descriptionMax')]

  if (form.link) {
    if (!/^https?:\/\/.+/.test(form.link)) e.link = [t('validation.linkInvalid')]
    else if (form.link.length > 500) e.link = [t('validation.linkMax')]
  }

  if (form.github_url) {
    if (!/^https?:\/\/.+/.test(form.github_url)) e.github_url = [t('validation.githubInvalid')]
    else if (form.github_url.length > 500) e.github_url = [t('validation.githubMax')]
  }

  if (form.start_date) {
    if (!isValidDate(form.start_date)) e.start_date = [t('validation.startDateInvalid')]
    else if (form.start_date < MIN_DATE) e.start_date = [t('validation.startDateMin')]
    else if (form.start_date > today()) e.start_date = [t('validation.startDateFuture')]
  }

  if (form.end_date) {
    if (!isValidDate(form.end_date)) e.end_date = [t('validation.endDateInvalid')]
    else if (form.start_date && form.end_date < form.start_date) e.end_date = [t('validation.endDateBeforeStart')]
    else if (form.end_date > MAX_FUTURE_DATE) e.end_date = [t('validation.endDateUnrealistic')]
  }

  return e
}

// ---------- CERTIFICATION ----------
export function validateCertification(form) {
  const e = {}
  if (!form.name || form.name.trim().length < 2) e.name = [t('validation.certNameRequired')]
  else if (form.name.trim().length > 255) e.name = [t('validation.certNameMax')]

  if (!form.organization || form.organization.trim().length < 2) e.organization = [t('validation.organizationRequired')]
  else if (form.organization.trim().length > 255) e.organization = [t('validation.organizationMax')]

  if (!form.issue_date) e.issue_date = [t('validation.issueDateRequired')]
  else if (!isValidDate(form.issue_date)) e.issue_date = [t('validation.issueDateInvalid')]
  else if (form.issue_date < MIN_DATE) e.issue_date = [t('validation.issueDateMin')]
  else if (form.issue_date > today()) e.issue_date = [t('validation.issueDateFuture')]

  if (form.expiration_date) {
    if (!isValidDate(form.expiration_date)) e.expiration_date = [t('validation.expirationDateInvalid')]
    else if (form.issue_date && form.expiration_date < form.issue_date) e.expiration_date = [t('validation.expirationDateBeforeIssue')]
  }

  return e
}

// ---------- VOLUNTEER ----------
export function validateVolunteer(form) {
  const e = {}
  if (!form.organization || form.organization.trim().length < 2) e.organization = [t('validation.organizationRequired')]
  else if (form.organization.trim().length > 255) e.organization = [t('validation.organizationMax')]

  if (!form.role || form.role.trim().length < 2) e.role = [t('validation.roleRequired')]
  else if (form.role.trim().length > 255) e.role = [t('validation.roleMax')]

  if (!form.start_date) e.start_date = [t('validation.startDateRequired')]
  else if (!isValidDate(form.start_date)) e.start_date = [t('validation.startDateInvalid')]
  else if (form.start_date < MIN_DATE) e.start_date = [t('validation.startDateMin')]
  else if (form.start_date > today()) e.start_date = [t('validation.startDateFuture')]

  if (form.end_date) {
    if (!isValidDate(form.end_date)) e.end_date = [t('validation.endDateInvalid')]
    else if (form.start_date && form.end_date < form.start_date) e.end_date = [t('validation.endDateBeforeStart')]
    else if (form.end_date > MAX_FUTURE_DATE) e.end_date = [t('validation.endDateUnrealistic')]
  }

  if (form.description && form.description.trim().length > 2000) e.description = [t('validation.descriptionMax')]

  return e
}

/**
 * Helper: returns true if errors object has any keys.
 */
export function hasErrors(errors) {
  return Object.keys(errors).length > 0
}
