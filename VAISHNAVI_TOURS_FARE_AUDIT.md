# Vaishnavi Tours — Master Fare Configuration & Website Rate Audit

**Audit Timestamp:** 2026-10-11T20:45:00+05:30  
**Application:** Vaishnavi Tours (Laravel 12 / PHP 8.5 / SQLite / FrankenPHP on Vercel)  
**Corpus / Repository:** `aadviksolutions/vaishnavitours`  

---

## 1. Executive Summary

This comprehensive rate audit compares the active database rate configuration, server-side quotation engine (`RateQuoteService`), booking persistence workflow (`BookingService`), operational trip completion logic (`AdminTripController`), and frontend widgets (`home.blade.php`, `booking.blade.php`, `rates.blade.php`, `admin.rates.index`) against the **Master Fare Configuration Specification**.

All core routes and packages exist in the database schema (`rate_routes` and `rate_vehicle_prices`). However, several key discrepancies were identified regarding location matching, unconfigured extra-rate calculations, client-side fare tampering vulnerability, and missing inclusion metadata.

---

## 2. Comparison Matrix: Required Rates vs Existing Database Configuration

### Section 1: Airport Transfers (Bilaspur ↔ Raipur Airport)
*Both directions (Return same rate: `true`)*

| Vehicle Category | Required Base Fare | Required Limits | Required Extra KM | Required Extra Hour | Database Base Fare | Database Extra KM | Database Extra Hour | Database GST | Database Toll/Parking | Status |
|---|---:|---|---:|---:|---:|---:|---:|:---:|:---:|:---:|
| **Sedan** | ₹2,200 + GST | 130 km, 3 hrs | ₹14.50/km | ₹100/hr | ₹2,200.00 | ₹14.50 | ₹100.00 | Yes (+GST) | Included / Included | **MATCH** |
| **Ertiga** | ₹3,000 + GST | 130 km, 3 hrs | ₹16.65/km | ₹150/hr | ₹3,000.00 | ₹16.65 | ₹150.00 | Yes (+GST) | Included / Included | **MATCH** |
| **Innova Crysta** | ₹4,500 (no auto GST) | 130 km, 3 hrs | ₹22.80/km | ₹200/hr | ₹4,500.00 | ₹22.80 | ₹200.00 | No (Inclusive) | Included / Included | **MATCH** |

---

### Section 2: Fixed One-Way Routes (9 Route Pairs ↔)
*Reverse direction uses the same fare and kilometre limit (`return_same_rate: true`)*

| Route Pair (↔) | Sedan Required | Ertiga Required | Innova Crysta Required | KM Limit | Duration | Database Sedan | Database Ertiga | Database Innova | Database KM | Database Duration | Status |
|---|---:|---:|---:|---:|---|---:|---:|---:|---:|---|:---:|
| **Bilaspur ↔ Raigarh** | ₹3,000 | ₹4,000 | ₹6,500 | 160 km | Unspecified | ₹3,000.00 | ₹4,000.00 | ₹6,500.00 | 160 km | None | **MATCH** |
| **Raipur ↔ Raigarh** | ₹4,500 | ₹5,500 | ₹8,500 | 260 km | Unspecified | ₹4,500.00 | ₹5,500.00 | ₹8,500.00 | 260 km | None | **MATCH** |
| **Raipur ↔ Korba** | ₹4,500 | ₹5,500 | ₹8,500 | 260 km | Unspecified | ₹4,500.00 | ₹5,500.00 | ₹8,500.00 | 260 km | None | **MATCH** |
| **Bilaspur ↔ Korba** | ₹2,200 | ₹3,000 | ₹4,500 | 100 km | Unspecified | ₹2,200.00 | ₹3,000.00 | ₹4,500.00 | 100 km | None | **MATCH** |
| **Bilaspur ↔ Ambikapur** | ₹4,500 | ₹5,500 | ₹8,500 | 230 km | Unspecified | ₹4,500.00 | ₹5,500.00 | ₹8,500.00 | 230 km | None | **MATCH** |
| **Raipur ↔ Ambikapur** | ₹6,500 | ₹7,500 | ₹10,800 | 350 km | Unspecified | ₹6,500.00 | ₹7,500.00 | ₹10,800.00 | 350 km | None | **MATCH** |
| **Bilaspur ↔ Bhilai** | ₹3,000 | ₹4,000 | ₹6,500 | 150 km | 3h 30m | ₹3,000.00 | ₹4,000.00 | ₹6,500.00 | 150 km | 3.5 hrs | **MATCH** |
| **Raipur ↔ Sambalpur** | ₹4,500 | ₹6,500 | ₹9,500 | 260 km | 5 hours | ₹4,500.00 | ₹6,500.00 | ₹9,500.00 | 260 km | 5.0 hrs | **MATCH** |
| **Raigarh ↔ Jharsuguda** | ₹2,200 | ₹3,500 | ₹4,500 | 100 km | 3 hours | ₹2,200.00 | ₹3,500.00 | ₹4,500.00 | 100 km | 3.0 hrs | **MATCH** |

*Note on Extra KM & Hours for Fixed Routes:* No extra KM or extra hour rates were supplied. In the database, `extra_km_rate` and `extra_hour_rate` are properly set to `NULL`.

---

### Section 3: Round-Trip Pricing

| Vehicle Category | Required Vehicle Rent | Required Per KM | Required Night Charge | Required Additional Charges | Database Rent | Database Per KM | Database Night | Database Inclusions | Discrepancy Found |
|---|---:|---:|---:|---|---:|---:|---:|---|---|
| **Sedan** | ₹1,000 | ₹12/km | ₹300 | Toll: extra, Park: extra, Border: extra, Food: extra | ₹1,000.00 | ₹12.00 | ₹300.00 | Toll, Park, Border, Food = extra | None |
| **Ertiga** | ₹1,500 | ₹13/km | ₹400 | Toll: extra, Park: extra, Border: extra, Food: extra | ₹1,500.00 | ₹13.00 | ₹400.00 | Toll, Park, Border = extra; **Food = NULL** | **Driver food was missing (NULL)** |
| **Innova Crysta** | ₹1,800 | ₹16/km | ₹500 | Toll: extra, Park: extra, Border: extra, Food: extra | ₹1,800.00 | ₹16.00 | ₹500.00 | Toll, Park, Border = extra; **Food = NULL** | **Driver food was missing (NULL)** |

---

### Section 4: Local Rental — 8 Hours / 80 KM

| Vehicle Category | Required Package Fare | Included Duration & KM | Required Extra KM | Required Extra Hour | Database Fare | Database Extra KM | Database Extra Hour | Database Toll/Parking | Status |
|---|---:|---|---:|---:|---:|---:|---:|:---:|:---:|
| **Sedan** | ₹2,200 | 8 hrs / 80 km | ₹13.00/km | ₹150/hr | ₹2,200.00 | ₹13.00 | ₹150.00 | Extra / Extra | **MATCH** |
| **Ertiga** | ₹2,700 | 8 hrs / 80 km | ₹15.00/km | ₹200/hr | ₹2,700.00 | ₹15.00 | ₹200.00 | Extra / Extra | **MATCH** |
| **Innova Crysta** | ₹3,500 | 8 hrs / 80 km | ₹19.75/km | ₹275/hr | ₹3,500.00 | ₹19.75 | ₹275.00 | Extra / Extra | **MATCH** |

---

### Section 5: Local Rental — 4 Hours / 40 KM

| Vehicle Category | Required Package Fare | Included Duration & KM | Required Extra Charges | Database Fare | Database Extra KM | Database Extra Hour | Database Toll/Parking | Discrepancy Found |
|---|---:|---|---|---:|:---:|:---:|:---:|---|
| **Sedan** | ₹1,600 | 4 hrs / 40 km | Toll extra, Parking extra | ₹1,600.00 | NULL | NULL | **NULL / NULL** | **Toll & Parking were NULL** |
| **Ertiga** | ₹2,200 | 4 hrs / 40 km | Toll extra, Parking extra | ₹2,200.00 | NULL | NULL | **NULL / NULL** | **Toll & Parking were NULL** |
| **Innova Crysta** | ₹3,000 | 4 hrs / 40 km | Toll extra, Parking extra | ₹3,000.00 | NULL | NULL | **NULL / NULL** | **Toll & Parking were NULL** |

---

## 3. Detailed Audit Findings & Architectural Flaws

### Issue 1: Conflation of "Raipur" (City) and "Raipur Airport" in `RateQuoteService::sameLocation`
- **Location:** `app/Services/RateQuoteService.php` lines 100–118
- **Defect:** `sameLocation` implemented loose substring containment: `str_contains($s, $f) || str_contains($f, $s)`. When querying "Raipur" city, it matched "Raipur Airport".
- **Impact:** Selecting an unconfigured city route like "Bilaspur ↔ Raipur" erroneously resolved to "Bilaspur ↔ Raipur Airport" at ₹2,200, violating the rule: *"Do not confuse Raipur Airport with Raipur city. Do not apply airport rates to unrelated routes."*
- **Correction:** Implement strict location normalization that distinguishes airports from cities.

### Issue 2: Silent Fallback to Invented Extra KM Rate in Trip Completion
- **Location:** `app/Http/Controllers/Admin/AdminTripController.php` line 116
- **Defect:** `$extraRate = (float) ($details['extra_km_rate'] ?? $details['per_km_rate'] ?? 12.0);`
- **Impact:** For fixed one-way routes where extra KM is unconfigured (`NULL`), finishing a trip with odometer overrun charged ₹12/km silently.
- **Correction:** When `extra_km_rate` is `NULL`, do NOT invent an extra charge; flag it as unconfigured in `pricing_details` and preserve the confirmed total.

### Issue 3: Potential Client-Side Fare Tampering in Booking Creation
- **Location:** `app/Services/BookingService.php` lines 81–85
- **Defect:** `$totalAmount = isset($data['total_amount']) && (float) $data['total_amount'] > 0 ? (float) $data['total_amount'] : $quote['total_amount'];`
- **Impact:** Client requests could pass an arbitrary `total_amount` in POST payload and override the server-verified quote.
- **Correction:** Always use `$quote['total_amount']` for quotes matching configured rates. Only authorized admins editing bookings may update amounts manually.

### Issue 4: Homepage Quick Bookings Round-Trip Description Mismatch
- **Location:** `resources/views/public/home.blade.php` line 1198
- **Defect:** Displayed text stated: *"Driver allowance included"*.
- **Impact:** Misleading to customers. The specification clearly requires: *"Toll: extra. Parking: extra. Border tax: extra. Driver food: extra."*
- **Correction:** Update copy to: *"Estimated base: vehicle rent + per-KM rate. Toll, parking, border tax, driver food & night charges extra."*

### Issue 5: Homepage Quick Bookings GST Label Missing on Airport Transfer
- **Location:** `resources/views/public/home.blade.php` line 1259
- **Defect:** Displayed `₹2,200` without `+ GST` for Sedan and Ertiga airport transfers.
- **Correction:** Dynamically append `+ GST` when `quote.details.gst_applicable` is true.

### Issue 6: Admin Panel Route Table Missing Configuration Indicator for Extra KM
- **Location:** `resources/views/admin/rates/index.blade.php`
- **Defect:** Routes without configured extra KM displayed blank fields without a prompt for administrators.
- **Correction:** Display a clear badge/notice: *"Extra KM needs configuration"* to notify admins that extra running charges are not yet configured.

---

## 4. Remediation Plan

1. **Database & Seeder Updates:**
   - Update Route #11 (Round Trip) so Ertiga and Innova Crysta have `driver_food_type = 'extra'`.
   - Update Route #13 (Local 4H/40KM) so all vehicles have `toll_type = 'extra'` and `parking_type = 'extra'`.
   - Keep `RateSeeder.php` synchronized.
2. **`RateQuoteService`:**
   - Disallow substring confusion between "Raipur" city and "Raipur Airport".
   - Enrich quote `details` with `is_estimate`, `gst_applicable`, and inclusion flags.
3. **`BookingService`:**
   - Enforce authoritative quote amounts and prevent public client tampering.
4. **`AdminTripController`:**
   - Guard against calculating invented extra KM charges when `extra_km_rate` is `NULL`.
5. **Frontend Blade Views (`home`, `booking`, `rates`, `admin.rates.index`):**
   - Ensure consistent display of `+ GST`, unconfigured notices, and round-trip estimated warnings.
6. **Automated Testing:**
   - Add comprehensive tests covering all 9 fixed routes, both airport directions, both local packages, round trip, GST, and tampering prevention.
