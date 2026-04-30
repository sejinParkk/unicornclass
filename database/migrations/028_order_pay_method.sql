-- 028_order_pay_method.sql
-- 토스페이먼츠 결제수단 정보 컬럼 추가

ALTER TABLE lc_order
  ADD COLUMN `pay_method`        VARCHAR(50)  NULL DEFAULT NULL COMMENT '결제수단 (카드/가상계좌/계좌이체/휴대폰 등)' AFTER `toss_order_id`,
  ADD COLUMN `card_company`      VARCHAR(50)  NULL DEFAULT NULL COMMENT '카드사명 (삼성, 신한, KB국민 등)'            AFTER `pay_method`,
  ADD COLUMN `card_type`         VARCHAR(10)  NULL DEFAULT NULL COMMENT '카드 종류 (신용/체크/기프트)'               AFTER `card_company`,
  ADD COLUMN `card_number`       VARCHAR(25)  NULL DEFAULT NULL COMMENT '마스킹된 카드번호'                          AFTER `card_type`,
  ADD COLUMN `card_install`      TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '할부 개월수 (0=일시불)'               AFTER `card_number`,
  ADD COLUMN `card_approve_no`   VARCHAR(20)  NULL DEFAULT NULL COMMENT '카드 승인번호'                             AFTER `card_install`;
