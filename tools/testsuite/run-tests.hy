;;#! /usr/bin/env hy
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

(import [os [listdir walk linesep makedirs popen]]
        [os.path [isfile isdir join exists]]
        [sys [argv exit]]
        [getopt [getopt GetoptError]]
        [glob [iglob]]
        [fnmatch]
        [shutil [rmtree]]
        [ntpath [basename]]
        [termcolor [colored]]
        [difflib])

(def **version** "Quack test toolkit v0.0.1-alpha")
(def **file-pattern** "*.qtest")
(def **tmp-folder** "tmp")

(defn get-params [args]
  """
  Returns the parameters passed to the script (parsed)
  """
  (car
    (getopt args "v" ["dir=" "exe="])))

(defn version []
  """
  Test suite version
  """
  (print **version**))

(defn throw-error [message]
  """
  Handles the received errors while receiving input
  """
  (print message)
  (exit 1))

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

(defn run-tests [generator exe]
  """
    The test suite!
  """
  (setv tests 0)
  (setv failed 0)
  (setv passed 0)

  ; start by creating the folder to store our tests and then feed the compiler
  (create-tmp-folder)

  (for [file generator]
    (setv filename (basename file))
    (setv section (group-sections (file-get-contents file)))
    ; Store the source for future queries
    (persist-source filename (:source section))
    (setv command (-> exe (.replace "%s" (join **tmp-folder** (-> filename (+ ".tmp"))))))
    (setv output (-> (popen command) (.read) (.strip)))
    (setv stripped-to-compare (-> (:expect section) (.strip)))
    ; We have enough data to give the results

    (setv tests (inc tests))
    (if (= output stripped-to-compare)
      (do
        (setv passed (inc passed))
        (print (colored (-> "Pass: " (+ file)) "green")))
      (do
        (setv failed (inc failed))
        (print
          (colored
            (-> "Fail: " (+ file)) "red"))
        (print "Difference:")
        (setv output-list
          (-> output (.split linesep)))
        (setv expected-list
          (-> stripped-to-compare (.split linesep)))

        (setv d
          (-> difflib (.Differ)))
        (setv diff
          (-> d (.compare output-list expected-list)))
        (print
          (-> linesep (.join diff))))))

  (print
    (colored "\nResults: " :attrs ["bold" "underline"]))
  (print
    (colored
      (-> "Run:  " (+ (str tests))) :attrs ["bold"]))
  (print
    (colored
      (-> "Pass: " (+ (str passed))) :attrs ["bold"]))
  (print
    (colored
      (-> "Fail: " (+ (str failed))) :attrs ["bold"]))

  ; Dump garbage
  (delete-tmp-files)
  (exit failed))

(defn tuple-contains-key [needle haystack]
  """
  Tells if a tuple contains a key. Returns (False, nil) if not.
  Returns (True, value) if it does
  """
  (setv fst False)
  (setv snd nil)
  (for [(, k v) haystack]
    (if (= k needle)
      (do
        (setv fst True)
        (setv snd v)
        (break))))
  (, fst snd))

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
      ; Version
      (if (first (tuple-contains-key "-v" params))
        (version))
      ; Define directory params
      (setv dir-tuple (tuple-contains-key "--dir" params))
      (setv exe-tuple (tuple-contains-key "--exe" params))
      (if (->> (first dir-tuple) (and (first exe-tuple)))
        (do
          (setv dir (second dir-tuple))
          (setv exe (second exe-tuple))
          (let [[generator (get-all-test-files dir)]]
            (run-tests generator exe)))
        (throw-error "--dir and --exe are obligatory")))))
