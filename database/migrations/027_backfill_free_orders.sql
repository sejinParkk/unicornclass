-- 027_backfill_free_orders.sql
-- 기존 무료 수강 데이터(order_idx IS NULL)에 lc_order row 생성 후 연결

DELIMITER //

CREATE PROCEDURE _migrate_free_enrolls()
BEGIN
  DECLARE done       INT DEFAULT 0;
  DECLARE v_enroll   INT;
  DECLARE v_member   INT;
  DECLARE v_class    INT;
  DECLARE v_enrolled DATETIME;
  DECLARE v_order    INT;

  DECLARE cur CURSOR FOR
    SELECT enroll_idx, member_idx, class_idx, enrolled_at
    FROM lc_enroll
    WHERE type = 'free' AND order_idx IS NULL;

  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

  OPEN cur;
  loop_rows: LOOP
    FETCH cur INTO v_enroll, v_member, v_class, v_enrolled;
    IF done THEN LEAVE loop_rows; END IF;

    INSERT INTO lc_order (member_idx, class_idx, amount, amount_origin, status, paid_at, created_at)
    VALUES (v_member, v_class, 0, 0, 'free', v_enrolled, v_enrolled);

    SET v_order = LAST_INSERT_ID();

    UPDATE lc_enroll SET order_idx = v_order WHERE enroll_idx = v_enroll;
  END LOOP;
  CLOSE cur;
END//

DELIMITER ;

CALL _migrate_free_enrolls();
DROP PROCEDURE _migrate_free_enrolls;
