-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 05 Nov 2025 pada 06.22
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `skap_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `encrypted_message` text NOT NULL,
  `send_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `encrypted_message`, `send_at`) VALUES
(1, 2, 1, 'FCKiWs6c1LRnxjHnF3NfPXzymODFusEAP63UnwtlohQ=', '2025-11-04 13:21:45');

-- --------------------------------------------------------

--
-- Struktur dari tabel `secure_files`
--

CREATE TABLE `secure_files` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `original_file_name` varchar(255) NOT NULL,
  `encrypted_file_path` varchar(255) NOT NULL,
  `iv_hex` varchar(32) NOT NULL,
  `upload_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `secure_files`
--

INSERT INTO `secure_files` (`id`, `sender_id`, `receiver_id`, `original_file_name`, `encrypted_file_path`, `iv_hex`, `upload_at`) VALUES
(1, 1, 1, 'Coursera 6XS99LAEYQYN.pdf', '/uploads/files/0ca1ea1e079cde06427a9029b8ccbd567cbf3e38db5b52eb27f65dc9ca6570f8.enc', '512745fa7018995390d3fcd555ca44f4', '2025-11-04 13:40:50'),
(2, 1, 2, 'Coursera 6XS99LAEYQYN (1).pdf', '/uploads/files/6cd4aa87ee6653e66707743db4715a1ac27a4b14d0283ec93adfe5654fe97464.enc', '22ef6604b67a156d7a2601c149024365', '2025-11-05 03:11:23'),
(3, 2, 2, 'Kelingan Mantan NDX AKA Pop Punk Cover by Boedak Korporat.mp3', '/uploads/files/26cea521ca800b360120cc772839ed95372d757cf837813156b24c2a1dfbe224.enc', 'aa14d3c07ea21110b1ebb2d1f6c38ac8', '2025-11-05 05:14:56');

-- --------------------------------------------------------

--
-- Struktur dari tabel `stego_images`
--

CREATE TABLE `stego_images` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `image_name` varchar(255) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `upload_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `stego_images`
--

INSERT INTO `stego_images` (`id`, `sender_id`, `receiver_id`, `image_name`, `image_path`, `upload_at`) VALUES
(1, 1, 1, 'Ingkung RBPL-RAT.drawio.png', '/uploads/images/stego_1762263813_Ingkung RBPL-RAT.drawio.png', '2025-11-04 13:43:34'),
(2, 1, 2, 'Ingkung RBPL-ERD.drawio (2).png', '/uploads/images/stego_1762312309_Ingkung RBPL-ERD.drawio (2).png', '2025-11-05 03:11:50'),
(3, 2, 2, 'pp.png', '/uploads/images/stego_1762320053_pp.png', '2025-11-05 05:20:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `salt` varchar(32) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `salt`, `created_at`) VALUES
(1, 'zakinanda.faishal@gmail.com', '7bed3143b5d464a3e3fdea4c2da5a18eb3e4ad0f0c58d3ebd806ba2b8315bc64', 'b111b9ee1e287aa2238662fabe957920', '2025-11-04 12:30:39'),
(2, 'puki', '2a275940520453edb78caea5a0faec0fd640c59a9909b8ff1177add8fd617ef6', 'e3596bd4072caf7a920f30544a42dfbe', '2025-11-04 13:21:33');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indeks untuk tabel `secure_files`
--
ALTER TABLE `secure_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indeks untuk tabel `stego_images`
--
ALTER TABLE `stego_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `secure_files`
--
ALTER TABLE `secure_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `stego_images`
--
ALTER TABLE `stego_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `secure_files`
--
ALTER TABLE `secure_files`
  ADD CONSTRAINT `secure_files_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `secure_files_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `secure_files_ibfk_3` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `stego_images`
--
ALTER TABLE `stego_images`
  ADD CONSTRAINT `stego_images_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `stego_images_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `stego_images_ibfk_3` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
