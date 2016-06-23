#! /usr/bin/env hy
;;
;; Quack Compiler and toolkit
;; Copyright (C) 2016 Marcelo Camargo <marcelocamargo@linuxmail.org> and
;; CONTRIBUTORS.
;;
;; This file is part of Quack.
;;
;; Quack is free software: you can redistribute it and/or modify
;; it under the terms of the GNU General Public License as published by
;; the Free Software Foundation, either version 3 of the License, or
;; (at your option) any later version.
;;
;; Quack is distributed in the hope that it will be useful,
;; but WITHOUT ANY WARRANTY; without even the implied warranty of
;; MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
;; GNU General Public License for more details.
;;
;; You should have received a copy of the GNU General Public License
;; along with Quack.  If not, see <http://www.gnu.org/licenses/>.
;;

(import [os [listdir walk]]
        [os.path [isfile isdir join]]
        [sys [argv]]
        [getopt [getopt GetoptError]]
        [glob [iglob]]
        [fnmatch])

(def **version** "Quack test toolkit v0.0.1-alpha")
(def **file-pattern** "*.php")

; Return the parameters passed to the script
(defn get-params [args]
  (car
    (getopt args "v" ["dir="])))

; Output version
(defn version []
  (print **version**))


; Error handler
(defn throw-error [message]
  (print message))

; List the test files of a directory (recursive)
(defn get-all-test-files [dir]
  (if (not (isdir dir))
    (throw-error "Directory not found")
    (do
      (setv matches [])
      (for [(, root dirname filenames) (walk dir)]
        (for [filename (-> fnmatch (.filter filenames "*.php"))]
          (-> matches (.append (join root filename)))))
      matches)))

; Returns the clean content of a file
(defn file-get-contents [file]
  (with [[f (open file)]]
    (.read f)))

; Entry point
(defmain [&rest args]
  (try
    (setv params
      (get-params (cdr args)))
    (except [e GetoptError]
      (throw-error e))
    (else
      (for [(, k v) params]
        (if (= k "-v") (version))
        (if (= k "--dir")
          (let [[generator (get-all-test-files v)]]
            (for [file generator]
              (print (file-get-contents file)))))))))

