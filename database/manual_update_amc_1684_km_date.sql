-- Manual update: service_details km and date for AMC 1684 (amc_masters.id = 389)
-- 1st service is left UNCHANGED. Only 2nd, 3rd, 4th... are updated from 1st service's date/km.
-- Old vehicle: service 2 = service_1_date + 120 days, service_1_km + 4000; then +119 days and +4000 km each.

-- 1) Get 1st service date and km (use these values in step 2)
SELECT id, service_no, service_date, service_km
FROM service_details
WHERE amc_id = 389
ORDER BY service_no ASC;

-- 2) Compute from 1st service (do NOT update service_no 1):
--    If service 1 has service_date = '2026-02-24' and service_km = 22223 then:
--    service_no 2: date = 2026-02-24 + 120 days = 2026-06-24, km = 22223 + 4000 = 26223
--    service_no 3: date = 2026-06-24 + 119 days = 2026-10-21, km = 26223 + 4000 = 30223
--    service_no 4: date = 2026-10-21 + 119 days = 2027-02-17, km = 30223 + 4000 = 34223

-- 3) Update ONLY from 2nd service onwards (replace dates/km with your computed values from 1st service)
UPDATE service_details SET service_date = '2026-06-24 00:00:00', service_km = 26223 WHERE amc_id = 389 AND service_no = 2;
UPDATE service_details SET service_date = '2026-10-21 00:00:00', service_km = 30223 WHERE amc_id = 389 AND service_no = 3;
UPDATE service_details SET service_date = '2027-02-17 00:00:00', service_km = 34223 WHERE amc_id = 389 AND service_no = 4;
-- Add more lines for service_no 5, 6... if needed (each +119 days, +4000 km).
