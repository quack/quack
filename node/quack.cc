/**
 * Quack Compiler and toolkit
 * Copyright (C) 2015-2017 Quack and CONTRIBUTORS
 *
 * This file is part of Quack.
 *
 * Quack is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Quack is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Quack.  If not, see <http://www.gnu.org/licenses/>.
 */

#include <node.h>
#include <iostream>
#include <fstream>
#include <unistd.h>
#include <stdio.h>

namespace quack
{
using v8::Exception;
using v8::FunctionCallbackInfo;
using v8::Isolate;
using v8::Local;
using v8::Object;
using v8::String;
using v8::Value;

void Compile(const FunctionCallbackInfo<Value>& args) {
    Isolate* isolate = args.GetIsolate();
    String::Utf8Value param(args[0]->ToString());
    std::string source = std::string(*param);
    std::string intermediate = "intermediate.qk";
    std::ofstream outfile(intermediate);
    outfile << source;
    outfile.close();
    char buffer[128];
    std::string result = "";
    FILE* pipe = popen("php /bin/quack intermediate.qk", "r");
    while (!feof(pipe)) {
        if (fgets(buffer, 128, pipe) != NULL) {
            result += buffer;
        }
    }
    unsigned short exit_code = pclose(pipe) / 256;
    unlink(intermediate.c_str());

    if (1 == exit_code) {
        isolate->ThrowException(Exception::Error(String::NewFromUtf8(isolate, result.c_str())));
    } else {
        args.GetReturnValue().Set(String::NewFromUtf8(isolate, result.c_str()));
    }
}

void Initialize(Local<Object> exports, Local<Object> module) {
    NODE_SET_METHOD(module, "exports", Compile);
}

NODE_MODULE(QUACK, Initialize)
}
