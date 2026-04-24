-- New changes: service_details km and date for AMC 1549 (amc_masters.id = 585)
-- 1st service is left UNCHANGED. Only 2nd, 3rd, 4th... are updated from 1st service's date/km.
-- Same logic: service 2 = service_1_date + 120 days, service_1_km + 4000; then +119 days and +4000 km each.

-- 1) Get 1st service date and km (use these values in step 2)
SELECT id, service_no, service_date, service_km
FROM service_details
WHERE amc_id = 585
ORDER BY service_no ASC;

-- 2) Compute from 1st service (do NOT update service_no 1):
--    1st service: service_date = 2026-02-26, service_km = 319 (unchanged)
--    service_no 2: date = 2026-02-26 + 120 days = 2026-06-26, km = 319 + 4000 = 4319
--    service_no 3: date = 2026-06-26 + 119 days = 2026-10-23, km = 4319 + 4000 = 8319
--    service_no 4: date = 2026-10-23 + 119 days = 2027-02-19, km = 8319 + 4000 = 12319

-- 3) Update ONLY from 2nd service onwards
UPDATE service_details SET service_date = '2026-06-26 00:00:00', service_km = 4319 WHERE amc_id = 585 AND service_no = 2;
UPDATE service_details SET service_date = '2026-10-23 00:00:00', service_km = 8319 WHERE amc_id = 585 AND service_no = 3;
UPDATE service_details SET service_date = '2027-02-19 00:00:00', service_km = 12319 WHERE amc_id = 585 AND service_no = 4;
