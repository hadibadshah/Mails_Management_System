import { EmailAccount, OrderRecord } from '../types';
import { REAL_HOSTINGER_ACCOUNTS, REAL_HOSTINGER_ORDERS } from './liveHostingerData';

export const ADMIN_USERNAME = 'Hadi';
export const ADMIN_PASSWORD = '91199119';

export const CLIENT_USERNAME = 'rana asim';
export const CLIENT_PASSWORD = 'rana@123';

export const SECURE_EXTRACTION_PIN = '1234';

export const MANAGED_DOMAINS = ['basis5.ch', 'adlover.site'] as const;

export const INITIAL_EMAIL_ACCOUNTS: EmailAccount[] = REAL_HOSTINGER_ACCOUNTS;

export const SAMPLE_CSV_DATA = `email,password,recovery email
alpha_01@basis5.ch,VaultP@ss101,recovery1@gmail.com
alpha_02@basis5.ch,VaultP@ss102,recovery2@gmail.com
alpha_03@basis5.ch,VaultP@ss103,recovery3@gmail.com
beta_01@adlover.site,CyberSec!201,sec_rec1@yahoo.com
beta_02@adlover.site,CyberSec!202,sec_rec2@yahoo.com
beta_03@adlover.site,CyberSec!203,sec_rec3@yahoo.com`;

export const INITIAL_ORDERS: OrderRecord[] = REAL_HOSTINGER_ORDERS;
