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

(import [os [listdir walk linesep makedirs]]
        [os.path [isfile isdir join exists]]
        [sys [argv]]
        [getopt [getopt GetoptError]]
        [glob [iglob]]
        [fnmatch]
        [shutil [rmtree]]
        [ntpath [basename]])

(def **version** "Quack test toolkit v0.0.1-alpha")
(def **file-pattern** "*.qtest")
(def **tmp-folder** "tmp")

(defn get-params [args]
  """
  Returns the parameters passed to the script (parsed)
  """
  (car
    (getopt args "v" ["dir="])))

(defn version []
  """
  Test suite version
  """
  (print **version**))

(defn throw-error [message]
  """
  Handles the received errors while receiving input
  """
  (print message))

(defn get-all-test-files [dir]
  """
  Lists the path + filename of all the files in the directory, but
  recursively
  """
  (if (not (isdir dir))
    (throw-error "Directory not found")
    (do
      (setv matches [])
      (for [(, root dirname filenames) (walk dir)]
        (for [filename (-> fnmatch (.filter filenames **file-pattern**))]
          (-> matches (.append (join root filename)))))
      matches)))

(defn file-get-contents [file]
  """
  Returns the clean content of a file
  """
  (with [[f (open file)]]
    (.read f)))

(defn group-sections [input]
  """
  Receives an input and group the sections
  """
  (setv tok :none)
  (setv describe []) ; File description
  (setv source [])   ; Source code
  (setv expect [])   ; Expected output
  (let [[lines (-> input (.split linesep))]]
    ; Parser is cumulative. You can have multiple and isolated sections
    (for [line lines]
      (cond
        [(= line "%%describe")
          (setv tok :describe)]
        [(= line "%%source")
          (setv tok :source)]
        [(= line "%%expect")
          (setv tok :expect)]
        [True
          (cond
            [(= tok :describe)
              (-> describe (.append line))]
            [(= tok :source)
              (-> source (.append line))]
            [(= tok :expect)
              (-> expect (.append line))])])))
  (let [[joiner (fn [lst] (-> linesep (.join lst)))]]
   { :describe (joiner describe)
     :source (joiner source)
     :expect (joiner expect) }))

(defn create-tmp-folder []
  """
  Creates the temp folder for the tests
  """
  (if (not (exists **tmp-folder**))
    (makedirs **tmp-folder**)))

(defn delete-tmp-files []
  """
  Deletes the test folder and its contents recursively
  """
  (if (exists **tmp-folder**)
    (rmtree **tmp-folder**)))

; Gets a grouped section and saves the input result to a temp file
(defn persist-source [name source]
  (with [[f (open (join **tmp-folder** (-> name (+ ".tmp"))) "w")]]
    (-> f (.write source))))

(defn run-tests [generator]
  """
    The test suite!
  """
  ; start by creating the folder to store our tests and then feed the compiler
  (create-tmp-folder)

  (for [file generator]
    (setv filename (basename file))
    (let [[section (group-sections (file-get-contents file))]]
      ; Store the source for future queries
      (persist-source filename (:source section))))

  ; Dump garbage
  ; (delete-tmp-files)
  )

(defmain [&rest args]
  """
  Entry point
  """
  (try
    ; Parse params
    (setv params
      (get-params (cdr args)))
    (except [e GetoptError]
      (throw-error e))
    (else
      (for [(, k v) params]
        (if (= k "-v") (version))
        (if (= k "--dir")
          ; Start the analysis
          (let [[generator (get-all-test-files v)]]
            (run-tests generator)))))))

