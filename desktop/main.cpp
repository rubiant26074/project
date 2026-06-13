#include <windows.h>

namespace {
    constexpr wchar_t kUrl[] = L"https://pm.berkahcipta.co.id";

    LRESULT CALLBACK WndProc(HWND hwnd, UINT message, WPARAM wParam, LPARAM lParam) {
        switch (message) {
            case WM_CREATE: {
                CreateWindowExW(0, L"STATIC", L"Project Control Manager",
                    WS_CHILD | WS_VISIBLE | SS_LEFT,
                    18, 12, 420, 24, hwnd, nullptr,
                    reinterpret_cast<LPCREATESTRUCTW>(lParam)->hInstance, nullptr);

                CreateWindowExW(0, L"STATIC", L"Dashboard Project Management",
                    WS_CHILD | WS_VISIBLE | SS_LEFT,
                    18, 38, 420, 18, hwnd, nullptr,
                    reinterpret_cast<LPCREATESTRUCTW>(lParam)->hInstance, nullptr);

                CreateWindowExW(0, L"BUTTON", L"Open PM",
                    WS_CHILD | WS_VISIBLE | BS_PUSHBUTTON,
                    18, 72, 120, 32, hwnd,
                    reinterpret_cast<HMENU>(1001),
                    reinterpret_cast<LPCREATESTRUCTW>(lParam)->hInstance, nullptr);

                CreateWindowExW(0, L"BUTTON", L"Refresh",
                    WS_CHILD | WS_VISIBLE | BS_PUSHBUTTON,
                    150, 72, 120, 32, hwnd,
                    reinterpret_cast<HMENU>(1002),
                    reinterpret_cast<LPCREATESTRUCTW>(lParam)->hInstance, nullptr);

                CreateWindowExW(0, L"BUTTON", L"Open Browser",
                    WS_CHILD | WS_VISIBLE | BS_PUSHBUTTON,
                    282, 72, 150, 32, hwnd,
                    reinterpret_cast<HMENU>(1003),
                    reinterpret_cast<LPCREATESTRUCTW>(lParam)->hInstance, nullptr);

                return 0;
            }

            case WM_COMMAND:
                if (LOWORD(wParam) == 1001 || LOWORD(wParam) == 1002 || LOWORD(wParam) == 1003) {
                    ShellExecuteW(hwnd, L"open", kUrl, nullptr, nullptr, SW_SHOWNORMAL);
                }
                return 0;

            case WM_DESTROY:
                PostQuitMessage(0);
                return 0;
        }

        return DefWindowProcW(hwnd, message, wParam, lParam);
    }
}

int WINAPI wWinMain(HINSTANCE hInstance, HINSTANCE, PWSTR, int nCmdShow) {
    const wchar_t kClassName[] = L"ProjectDesktopWindow";

    WNDCLASSW wc{};
    wc.lpfnWndProc = WndProc;
    wc.hInstance = hInstance;
    wc.hCursor = LoadCursorW(nullptr, IDC_ARROW);
    wc.hIcon = LoadIconW(nullptr, IDI_APPLICATION);
    wc.hbrBackground = reinterpret_cast<HBRUSH>(COLOR_WINDOW + 1);
    wc.lpszClassName = kClassName;
    RegisterClassW(&wc);

    HWND hwnd = CreateWindowExW(
        0,
        kClassName,
        L"Project Control Manager - Desktop",
        WS_OVERLAPPEDWINDOW,
        CW_USEDEFAULT,
        CW_USEDEFAULT,
        520,
        260,
        nullptr,
        nullptr,
        hInstance,
        nullptr);

    if (!hwnd) {
        return 1;
    }

    ShowWindow(hwnd, nCmdShow);
    UpdateWindow(hwnd);

    MSG msg{};
    while (GetMessageW(&msg, nullptr, 0, 0) > 0) {
        TranslateMessage(&msg);
        DispatchMessageW(&msg);
    }

    return static_cast<int>(msg.wParam);
}
