cmake_minimum_required(VERSION 3.20)
project(ProjectDesktop LANGUAGES CXX)

set(CMAKE_CXX_STANDARD 20)
set(CMAKE_CXX_STANDARD_REQUIRED ON)

add_executable(ProjectDesktop main.cpp)

target_link_libraries(ProjectDesktop PRIVATE
  windowsapp
  webview2
  winhttp
)

if (MSVC)
  target_compile_definitions(ProjectDesktop PRIVATE _UNICODE UNICODE)
endif()
