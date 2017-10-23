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
