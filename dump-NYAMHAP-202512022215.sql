--
-- PostgreSQL database dump
--

\restrict yZiwoJVpRvI4eI5l3ScayJAAGh8l9rNdkaIrvFQZbE9lWGhDAqI09R7b1YMAUz7

-- Dumped from database version 18.0
-- Dumped by pg_dump version 18.0

-- Started on 2025-12-02 22:15:22

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 222 (class 1259 OID 16854)
-- Name: bahanbaku; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.bahanbaku (
    bahanid character varying(10) NOT NULL,
    namabahan character varying(50) NOT NULL,
    satuan character varying(20) NOT NULL,
    stok double precision,
    hargaperunit integer,
    CONSTRAINT bahanbaku_stok_check CHECK ((stok >= (0)::double precision))
);


ALTER TABLE public.bahanbaku OWNER TO postgres;

--
-- TOC entry 231 (class 1259 OID 16975)
-- Name: detailpesanan; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.detailpesanan (
    pesananid character varying(10) NOT NULL,
    menuid character varying(10) NOT NULL,
    pilihanmenu character varying(100) NOT NULL,
    jumlahmenu integer,
    CONSTRAINT detailpesanan_jumlahmenu_check CHECK ((jumlahmenu > 0))
);


ALTER TABLE public.detailpesanan OWNER TO postgres;

--
-- TOC entry 221 (class 1259 OID 16842)
-- Name: menu; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.menu (
    menuid character varying(10) NOT NULL,
    namamenu character varying(100) NOT NULL,
    harga integer NOT NULL,
    deskripsi text NOT NULL,
    CONSTRAINT menu_harga_check CHECK ((harga > 0))
);


ALTER TABLE public.menu OWNER TO postgres;

--
-- TOC entry 220 (class 1259 OID 16832)
-- Name: pelanggan; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.pelanggan (
    pelangganid character varying(10) NOT NULL,
    namap character varying(100) NOT NULL,
    nohandphonep character varying(15) NOT NULL
);


ALTER TABLE public.pelanggan OWNER TO postgres;

--
-- TOC entry 230 (class 1259 OID 16960)
-- Name: pembayaran; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.pembayaran (
    pembayaranid character varying(10) NOT NULL,
    pesananid character varying(10) NOT NULL,
    tanggalpembayaran date DEFAULT CURRENT_DATE,
    metodepembayaran character varying(20),
    statuspembayaran character varying(20),
    CONSTRAINT pembayaran_metodepembayaran_check CHECK (((metodepembayaran)::text = ANY ((ARRAY['Cash'::character varying, 'Transfer'::character varying, 'QRIS'::character varying, 'E-Wallet'::character varying])::text[]))),
    CONSTRAINT pembayaran_statuspembayaran_check CHECK (((statuspembayaran)::text = ANY ((ARRAY['Lunas'::character varying, 'Pending'::character varying, 'Gagal'::character varying])::text[])))
);


ALTER TABLE public.pembayaran OWNER TO postgres;

--
-- TOC entry 229 (class 1259 OID 16945)
-- Name: periklanan; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.periklanan (
    periklananid character varying(10) NOT NULL,
    staffid character varying(10) NOT NULL,
    mediaperiklanan character varying(50) NOT NULL,
    tanggalmulai date NOT NULL,
    tanggalselesai date,
    biaya integer,
    CONSTRAINT periklanan_check CHECK ((tanggalselesai >= tanggalmulai))
);


ALTER TABLE public.periklanan OWNER TO postgres;

--
-- TOC entry 228 (class 1259 OID 16931)
-- Name: pesanan; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.pesanan (
    pesananid character varying(10) NOT NULL,
    pelangganid character varying(10) NOT NULL,
    tanggal date DEFAULT CURRENT_DATE,
    totalharga integer,
    CONSTRAINT pesanan_totalharga_check CHECK ((totalharga >= 0))
);


ALTER TABLE public.pesanan OWNER TO postgres;

--
-- TOC entry 232 (class 1259 OID 16994)
-- Name: resep; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.resep (
    menuid character varying(10) NOT NULL,
    bahanid character varying(10) NOT NULL,
    jumlahbahan double precision,
    CONSTRAINT resep_jumlahbahan_check CHECK ((jumlahbahan > (0)::double precision))
);


ALTER TABLE public.resep OWNER TO postgres;

--
-- TOC entry 219 (class 1259 OID 16819)
-- Name: staff; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.staff (
    staffid character varying(10) NOT NULL,
    namas character varying(100) NOT NULL,
    nohandphones character varying(15) NOT NULL,
    email character varying(100) NOT NULL,
    passwordhash character varying(255),
    rolekode character varying(10)
);


ALTER TABLE public.staff OWNER TO postgres;

--
-- TOC entry 223 (class 1259 OID 16865)
-- Name: staffkeuangan; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.staffkeuangan (
    staffid character varying(10) NOT NULL,
    jenistransaksi character varying(20),
    tanggaltransaksi date NOT NULL,
    CONSTRAINT staffkeuangan_jenistransaksi_check CHECK (((jenistransaksi)::text = ANY ((ARRAY['Penjualan'::character varying, 'Pengeluaran'::character varying])::text[])))
);


ALTER TABLE public.staffkeuangan OWNER TO postgres;

--
-- TOC entry 227 (class 1259 OID 16918)
-- Name: staffmarketing; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.staffmarketing (
    staffid character varying(10) NOT NULL,
    jeniskampanye character varying(100) NOT NULL,
    budget integer,
    CONSTRAINT staffmarketing_budget_check CHECK ((budget >= 0))
);


ALTER TABLE public.staffmarketing OWNER TO postgres;

--
-- TOC entry 226 (class 1259 OID 16904)
-- Name: staffpemasok; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.staffpemasok (
    staffid character varying(10) NOT NULL,
    jenisbarang character varying(50) NOT NULL,
    tanggalpasokan date NOT NULL,
    metodepembayaran character varying(20),
    CONSTRAINT staffpemasok_metodepembayaran_check CHECK (((metodepembayaran)::text = ANY ((ARRAY['Cash'::character varying, 'Transfer'::character varying])::text[])))
);


ALTER TABLE public.staffpemasok OWNER TO postgres;

--
-- TOC entry 224 (class 1259 OID 16878)
-- Name: staffpenjualan; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.staffpenjualan (
    staffid character varying(10) NOT NULL,
    targetpenjualan integer,
    jumlahpenjualan integer,
    wilayahpenjualan character varying(50) NOT NULL,
    CONSTRAINT staffpenjualan_jumlahpenjualan_check CHECK ((jumlahpenjualan >= 0)),
    CONSTRAINT staffpenjualan_targetpenjualan_check CHECK ((targetpenjualan >= 0))
);


ALTER TABLE public.staffpenjualan OWNER TO postgres;

--
-- TOC entry 225 (class 1259 OID 16892)
-- Name: staffproduksi; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.staffproduksi (
    staffid character varying(10) NOT NULL,
    jumlahproduksi integer,
    CONSTRAINT staffproduksi_jumlahproduksi_check CHECK ((jumlahproduksi >= 0))
);


ALTER TABLE public.staffproduksi OWNER TO postgres;

--
-- TOC entry 5121 (class 0 OID 16854)
-- Dependencies: 222
-- Data for Name: bahanbaku; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.bahanbaku VALUES ('B001', 'Nasi', 'Kg', 50, 9000);
INSERT INTO public.bahanbaku VALUES ('B002', 'Ayam Suwir', 'Kg', 25, 35000);
INSERT INTO public.bahanbaku VALUES ('B003', 'Bumbu Balado', 'Pack', 40, 5000);
INSERT INTO public.bahanbaku VALUES ('B004', 'Ayam Geprek', 'Kg', 20, 35000);
INSERT INTO public.bahanbaku VALUES ('B005', 'Ayam Organik', 'Kg', 20, 45000);
INSERT INTO public.bahanbaku VALUES ('B006', 'Telur', 'Butir', 200, 2300);
INSERT INTO public.bahanbaku VALUES ('B007', 'Katsu Ayam', 'Pack', 60, 6000);
INSERT INTO public.bahanbaku VALUES ('B008', 'Mie Goreng', 'Pack', 150, 2000);
INSERT INTO public.bahanbaku VALUES ('B009', 'Bumbu Dasar', 'Pack', 80, 1500);
INSERT INTO public.bahanbaku VALUES ('B010', 'Roti Sandwich', 'Pack', 50, 4000);
INSERT INTO public.bahanbaku VALUES ('B011', 'Nori / Pembungkus Nasi', 'Pack', 100, 400);


--
-- TOC entry 5130 (class 0 OID 16975)
-- Dependencies: 231
-- Data for Name: detailpesanan; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.detailpesanan VALUES ('O001', 'M001', 'Pedas Manis', 1);
INSERT INTO public.detailpesanan VALUES ('O001', 'M005', 'Telur Matang', 1);
INSERT INTO public.detailpesanan VALUES ('O002', 'M002', 'Balado Pedas', 1);
INSERT INTO public.detailpesanan VALUES ('O002', 'M003', 'Level max', 1);
INSERT INTO public.detailpesanan VALUES ('O003', 'M006', 'Original', 1);
INSERT INTO public.detailpesanan VALUES ('O003', 'M001', 'Pedas', 1);
INSERT INTO public.detailpesanan VALUES ('O004', 'M005', 'Telur Setengah Matang', 1);
INSERT INTO public.detailpesanan VALUES ('O005', 'M007', 'Manis Pedas', 1);
INSERT INTO public.detailpesanan VALUES ('O006', 'M006', 'Original', 1);
INSERT INTO public.detailpesanan VALUES ('O007', 'M003', 'Original', 3);


--
-- TOC entry 5120 (class 0 OID 16842)
-- Dependencies: 221
-- Data for Name: menu; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.menu VALUES ('M001', 'Nasi Kepal Ayam Suwir', 5000, 'Nasi kepal isi ayam suwir pedas manis');
INSERT INTO public.menu VALUES ('M002', 'Nasi Kepal Ayam Balado', 5000, 'Nasi kepal isi tempe orek gurih');
INSERT INTO public.menu VALUES ('M003', 'Nasi Kepal Ayam Geprek', 7000, 'Nasi kepal isi ayam geprek level');
INSERT INTO public.menu VALUES ('M004', 'Nasi Kepal Ayam Ramah Lingkungan', 7000, 'Nasi kepal isi ayam karage crispy');
INSERT INTO public.menu VALUES ('M005', 'Sandwich Telur Ceplok', 7000, 'Roti lapis telur ceplok');
INSERT INTO public.menu VALUES ('M007', 'Nasi Kepal Mie Goreng', 5000, 'Nasi kepal isi mie goreng pedas manis');
INSERT INTO public.menu VALUES ('M006', 'Nasi Burger Katsu', 7000, 'Nasi burger isi ayam katsu');


--
-- TOC entry 5119 (class 0 OID 16832)
-- Dependencies: 220
-- Data for Name: pelanggan; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.pelanggan VALUES ('P001', 'Rafi', '081234888001');
INSERT INTO public.pelanggan VALUES ('P002', 'Keira', '081234888002');
INSERT INTO public.pelanggan VALUES ('P003', 'Arif', '081234888003');
INSERT INTO public.pelanggan VALUES ('P004', 'Putri', '081234888004');
INSERT INTO public.pelanggan VALUES ('P005', 'Amar', '081234888005');
INSERT INTO public.pelanggan VALUES ('P006', 'Abdul ', '081234567833');
INSERT INTO public.pelanggan VALUES ('P007', 'Fidoo', '084534411515111');


--
-- TOC entry 5129 (class 0 OID 16960)
-- Dependencies: 230
-- Data for Name: pembayaran; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.pembayaran VALUES ('BYR01', 'O001', '2025-02-01', 'QRIS', 'Lunas');
INSERT INTO public.pembayaran VALUES ('BYR03', 'O003', '2025-02-03', 'Cash', 'Lunas');
INSERT INTO public.pembayaran VALUES ('BYR04', 'O004', '2025-02-04', 'E-Wallet', 'Pending');
INSERT INTO public.pembayaran VALUES ('BYR05', 'O005', '2025-02-05', 'Cash', 'Lunas');
INSERT INTO public.pembayaran VALUES ('BYR02', 'O002', '2025-11-29', 'Transfer', 'Lunas');
INSERT INTO public.pembayaran VALUES ('BYR06', 'O006', '2025-12-02', 'Cash', 'Lunas');
INSERT INTO public.pembayaran VALUES ('BYR07', 'O007', '2025-12-02', 'Cash', 'Pending');


--
-- TOC entry 5128 (class 0 OID 16945)
-- Dependencies: 229
-- Data for Name: periklanan; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.periklanan VALUES ('AD001', 'S003', 'Group WA Kelas', '2025-01-05', '2025-01-10', 50000);
INSERT INTO public.periklanan VALUES ('AD002', 'S003', 'Instagram', '2025-01-07', '2025-01-31', 75000);
INSERT INTO public.periklanan VALUES ('AD003', 'S003', 'Instagram Staff', '2025-01-10', '2025-01-25', 90000);
INSERT INTO public.periklanan VALUES ('AD004', 'S003', 'Brosur', '2025-01-12', '2025-02-12', 60000);
INSERT INTO public.periklanan VALUES ('AD005', 'S003', 'Group WA Fakultas', '2025-01-15', '2025-02-15', 45000);


--
-- TOC entry 5127 (class 0 OID 16931)
-- Dependencies: 228
-- Data for Name: pesanan; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.pesanan VALUES ('O001', 'P001', '2025-02-01', 12000);
INSERT INTO public.pesanan VALUES ('O002', 'P002', '2025-02-02', 12000);
INSERT INTO public.pesanan VALUES ('O003', 'P003', '2025-02-03', 12000);
INSERT INTO public.pesanan VALUES ('O004', 'P004', '2025-02-04', 7000);
INSERT INTO public.pesanan VALUES ('O005', 'P005', '2025-02-05', 5000);
INSERT INTO public.pesanan VALUES ('O006', 'P007', '2025-12-02', 7000);
INSERT INTO public.pesanan VALUES ('O007', 'P006', '2025-12-02', 21000);


--
-- TOC entry 5131 (class 0 OID 16994)
-- Dependencies: 232
-- Data for Name: resep; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.resep VALUES ('M001', 'B001', 0.06);
INSERT INTO public.resep VALUES ('M001', 'B002', 0.025);
INSERT INTO public.resep VALUES ('M001', 'B009', 0.02);
INSERT INTO public.resep VALUES ('M001', 'B011', 1);
INSERT INTO public.resep VALUES ('M002', 'B001', 0.06);
INSERT INTO public.resep VALUES ('M002', 'B002', 0.025);
INSERT INTO public.resep VALUES ('M002', 'B003', 0.01);
INSERT INTO public.resep VALUES ('M002', 'B011', 1);
INSERT INTO public.resep VALUES ('M003', 'B001', 0.07);
INSERT INTO public.resep VALUES ('M003', 'B004', 0.03);
INSERT INTO public.resep VALUES ('M003', 'B009', 0.02);
INSERT INTO public.resep VALUES ('M003', 'B011', 1);
INSERT INTO public.resep VALUES ('M004', 'B001', 0.07);
INSERT INTO public.resep VALUES ('M004', 'B005', 0.025);
INSERT INTO public.resep VALUES ('M004', 'B009', 0.02);
INSERT INTO public.resep VALUES ('M004', 'B011', 1);
INSERT INTO public.resep VALUES ('M005', 'B010', 2);
INSERT INTO public.resep VALUES ('M005', 'B006', 1);
INSERT INTO public.resep VALUES ('M005', 'B009', 0.02);
INSERT INTO public.resep VALUES ('M006', 'B001', 0.06);
INSERT INTO public.resep VALUES ('M006', 'B007', 0.5);
INSERT INTO public.resep VALUES ('M006', 'B009', 0.02);
INSERT INTO public.resep VALUES ('M006', 'B011', 1);
INSERT INTO public.resep VALUES ('M007', 'B001', 0.05);
INSERT INTO public.resep VALUES ('M007', 'B008', 0.5);
INSERT INTO public.resep VALUES ('M007', 'B009', 0.02);
INSERT INTO public.resep VALUES ('M007', 'B011', 1);


--
-- TOC entry 5118 (class 0 OID 16819)
-- Dependencies: 219
-- Data for Name: staff; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.staff VALUES ('S001', 'Dzulfikar Najib', '081234567820', 'dzulfikar@staff.com', '123', 'Founder');
INSERT INTO public.staff VALUES ('S008', 'Dzikri', '082213432523', 'dzikri@staff.com', '123', 'SPem');
INSERT INTO public.staff VALUES ('S007', 'Gilang', '082213432532', 'gilang@staff.com', '123', 'SPem');
INSERT INTO public.staff VALUES ('S009', 'Dzikri2', '082213432529', 'dzikri2@staff.com', '123', 'SPem');
INSERT INTO public.staff VALUES ('S002', 'Siti Aminah', '081234567802', 'siti@staff.com', '123', 'SPem');
INSERT INTO public.staff VALUES ('S003', 'Rudi Hartono', '081234567803', 'rudi@staff.com', '123', 'SMar');
INSERT INTO public.staff VALUES ('S004', 'Andi Saputra', '081234567804', 'andi@staff.com', '123', 'SKeu');
INSERT INTO public.staff VALUES ('S005', 'Nina Kartika', '081234567805', 'nina@staff.com', '123', 'SProd');
INSERT INTO public.staff VALUES ('S006', 'Agus Pratama', '081234567806', 'agus@staff.com', '123', 'SPen');


--
-- TOC entry 5122 (class 0 OID 16865)
-- Dependencies: 223
-- Data for Name: staffkeuangan; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.staffkeuangan VALUES ('S004', 'Penjualan', '2025-02-04');


--
-- TOC entry 5126 (class 0 OID 16918)
-- Dependencies: 227
-- Data for Name: staffmarketing; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.staffmarketing VALUES ('S003', 'Stand Event', 1800000);


--
-- TOC entry 5125 (class 0 OID 16904)
-- Dependencies: 226
-- Data for Name: staffpemasok; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.staffpemasok VALUES ('S002', 'Ayam Suwir / Ayam Segar', '2025-01-07', 'Cash');


--
-- TOC entry 5123 (class 0 OID 16878)
-- Dependencies: 224
-- Data for Name: staffpenjualan; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.staffpenjualan VALUES ('S006', 90, 75, 'Tangerang');


--
-- TOC entry 5124 (class 0 OID 16892)
-- Dependencies: 225
-- Data for Name: staffproduksi; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.staffproduksi VALUES ('S005', 320);


--
-- TOC entry 4936 (class 2606 OID 16864)
-- Name: bahanbaku bahanbaku_namabahan_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bahanbaku
    ADD CONSTRAINT bahanbaku_namabahan_key UNIQUE (namabahan);


--
-- TOC entry 4938 (class 2606 OID 16862)
-- Name: bahanbaku bahanbaku_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bahanbaku
    ADD CONSTRAINT bahanbaku_pkey PRIMARY KEY (bahanid);


--
-- TOC entry 4956 (class 2606 OID 16983)
-- Name: detailpesanan detailpesanan_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.detailpesanan
    ADD CONSTRAINT detailpesanan_pkey PRIMARY KEY (pesananid, menuid);


--
-- TOC entry 4934 (class 2606 OID 16853)
-- Name: menu menu_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.menu
    ADD CONSTRAINT menu_pkey PRIMARY KEY (menuid);


--
-- TOC entry 4930 (class 2606 OID 16841)
-- Name: pelanggan pelanggan_nohandphonep_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pelanggan
    ADD CONSTRAINT pelanggan_nohandphonep_key UNIQUE (nohandphonep);


--
-- TOC entry 4932 (class 2606 OID 16839)
-- Name: pelanggan pelanggan_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pelanggan
    ADD CONSTRAINT pelanggan_pkey PRIMARY KEY (pelangganid);


--
-- TOC entry 4954 (class 2606 OID 16969)
-- Name: pembayaran pembayaran_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pembayaran
    ADD CONSTRAINT pembayaran_pkey PRIMARY KEY (pembayaranid);


--
-- TOC entry 4952 (class 2606 OID 16954)
-- Name: periklanan periklanan_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.periklanan
    ADD CONSTRAINT periklanan_pkey PRIMARY KEY (periklananid);


--
-- TOC entry 4950 (class 2606 OID 16939)
-- Name: pesanan pesanan_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pesanan
    ADD CONSTRAINT pesanan_pkey PRIMARY KEY (pesananid);


--
-- TOC entry 4958 (class 2606 OID 17001)
-- Name: resep resep_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resep
    ADD CONSTRAINT resep_pkey PRIMARY KEY (menuid, bahanid);


--
-- TOC entry 4924 (class 2606 OID 16831)
-- Name: staff staff_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staff
    ADD CONSTRAINT staff_email_key UNIQUE (email);


--
-- TOC entry 4926 (class 2606 OID 16829)
-- Name: staff staff_nohandphones_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staff
    ADD CONSTRAINT staff_nohandphones_key UNIQUE (nohandphones);


--
-- TOC entry 4928 (class 2606 OID 16827)
-- Name: staff staff_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staff
    ADD CONSTRAINT staff_pkey PRIMARY KEY (staffid);


--
-- TOC entry 4940 (class 2606 OID 16872)
-- Name: staffkeuangan staffkeuangan_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staffkeuangan
    ADD CONSTRAINT staffkeuangan_pkey PRIMARY KEY (staffid);


--
-- TOC entry 4948 (class 2606 OID 16925)
-- Name: staffmarketing staffmarketing_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staffmarketing
    ADD CONSTRAINT staffmarketing_pkey PRIMARY KEY (staffid);


--
-- TOC entry 4946 (class 2606 OID 16912)
-- Name: staffpemasok staffpemasok_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staffpemasok
    ADD CONSTRAINT staffpemasok_pkey PRIMARY KEY (staffid);


--
-- TOC entry 4942 (class 2606 OID 16886)
-- Name: staffpenjualan staffpenjualan_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staffpenjualan
    ADD CONSTRAINT staffpenjualan_pkey PRIMARY KEY (staffid);


--
-- TOC entry 4944 (class 2606 OID 16898)
-- Name: staffproduksi staffproduksi_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staffproduksi
    ADD CONSTRAINT staffproduksi_pkey PRIMARY KEY (staffid);


--
-- TOC entry 4967 (class 2606 OID 16989)
-- Name: detailpesanan detailpesanan_menuid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.detailpesanan
    ADD CONSTRAINT detailpesanan_menuid_fkey FOREIGN KEY (menuid) REFERENCES public.menu(menuid) ON DELETE CASCADE;


--
-- TOC entry 4968 (class 2606 OID 16984)
-- Name: detailpesanan detailpesanan_pesananid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.detailpesanan
    ADD CONSTRAINT detailpesanan_pesananid_fkey FOREIGN KEY (pesananid) REFERENCES public.pesanan(pesananid) ON DELETE CASCADE;


--
-- TOC entry 4966 (class 2606 OID 16970)
-- Name: pembayaran pembayaran_pesananid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pembayaran
    ADD CONSTRAINT pembayaran_pesananid_fkey FOREIGN KEY (pesananid) REFERENCES public.pesanan(pesananid) ON DELETE CASCADE;


--
-- TOC entry 4965 (class 2606 OID 16955)
-- Name: periklanan periklanan_staffid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.periklanan
    ADD CONSTRAINT periklanan_staffid_fkey FOREIGN KEY (staffid) REFERENCES public.staff(staffid);


--
-- TOC entry 4964 (class 2606 OID 16940)
-- Name: pesanan pesanan_pelangganid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pesanan
    ADD CONSTRAINT pesanan_pelangganid_fkey FOREIGN KEY (pelangganid) REFERENCES public.pelanggan(pelangganid) ON DELETE CASCADE;


--
-- TOC entry 4969 (class 2606 OID 17007)
-- Name: resep resep_bahanid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resep
    ADD CONSTRAINT resep_bahanid_fkey FOREIGN KEY (bahanid) REFERENCES public.bahanbaku(bahanid) ON DELETE CASCADE;


--
-- TOC entry 4970 (class 2606 OID 17002)
-- Name: resep resep_menuid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resep
    ADD CONSTRAINT resep_menuid_fkey FOREIGN KEY (menuid) REFERENCES public.menu(menuid) ON DELETE CASCADE;


--
-- TOC entry 4959 (class 2606 OID 16873)
-- Name: staffkeuangan staffkeuangan_staffid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staffkeuangan
    ADD CONSTRAINT staffkeuangan_staffid_fkey FOREIGN KEY (staffid) REFERENCES public.staff(staffid);


--
-- TOC entry 4963 (class 2606 OID 16926)
-- Name: staffmarketing staffmarketing_staffid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staffmarketing
    ADD CONSTRAINT staffmarketing_staffid_fkey FOREIGN KEY (staffid) REFERENCES public.staff(staffid);


--
-- TOC entry 4962 (class 2606 OID 16913)
-- Name: staffpemasok staffpemasok_staffid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staffpemasok
    ADD CONSTRAINT staffpemasok_staffid_fkey FOREIGN KEY (staffid) REFERENCES public.staff(staffid);


--
-- TOC entry 4960 (class 2606 OID 16887)
-- Name: staffpenjualan staffpenjualan_staffid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staffpenjualan
    ADD CONSTRAINT staffpenjualan_staffid_fkey FOREIGN KEY (staffid) REFERENCES public.staff(staffid);


--
-- TOC entry 4961 (class 2606 OID 16899)
-- Name: staffproduksi staffproduksi_staffid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staffproduksi
    ADD CONSTRAINT staffproduksi_staffid_fkey FOREIGN KEY (staffid) REFERENCES public.staff(staffid);


-- Completed on 2025-12-02 22:15:23

--
-- PostgreSQL database dump complete
--

\unrestrict yZiwoJVpRvI4eI5l3ScayJAAGh8l9rNdkaIrvFQZbE9lWGhDAqI09R7b1YMAUz7

